<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-sync.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage util
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* CCSync - class for keeping cached upload counts in sync with the rest of the system.
*
* This class is called whenever a change has been make to:
*
*  - uploads table 
*  - remix tree
*  - upload ratings
*
* It tracks ANY possible change and updates all the places in the tables
* that store that kind of information
*
*
**/
class CCSync
{
    /**
    * Called after a file has been deleted and remix tree is up to date.
    * 
    * The $record parameter is for the now 'bad' record so the upload_id
    * is no longer valid. However, the we the upload_user and remix_parents/
    * children fields to update the various needed players.
    *
    * @param $record Values related to now deleted file include remix parents/children  
    */
    public static function Delete($record)
    {
        $upload_ids = array();
        $user_ids[] = $record['upload_user'];
        if( !empty($record['tree_ids']) )
        {
            foreach( $record['tree_ids'] as $R )
            {
                $upload_ids[] = $R['upload_id'];
                $user_ids[] = $R['upload_user'];
            }
        }
        $upload_ids = array_unique($upload_ids);
        $user_ids   = array_unique($user_ids);
        $upload_id = $record['upload_id'];

        // Update the internal counters for any former parent,
        // child of the former upload...

        if( !empty($upload_ids) )
        {
            $queries = array();
            $str_ids = join(',',$upload_ids);
            $sql =<<<END
                SELECT COUNT(*) as upload_num_remixes, tree_parent as upload_id
                   FROM cc_tbl_tree
                   WHERE tree_parent IN ({$str_ids})
                   GROUP BY tree_parent
END;

            $queries[] = array( $upload_id,
                                'upload_num_remixes',
                                $sql );
            $sql =<<<END
                SELECT COUNT(*) as upload_num_sources, tree_child as upload_id
                   FROM cc_tbl_tree
                   WHERE tree_child IN ({$str_ids})
                   GROUP BY tree_child
END;

            $queries[] = array( $upload_id,
                                'upload_num_sources',
                                $sql );

            $sql =<<<END
                SELECT COUNT(*) as upload_num_pool_sources, pool_tree_child as upload_id
                   FROM cc_tbl_pool_tree
                   WHERE pool_tree_child IN ({$str_ids})
                   GROUP BY pool_tree_child
END;
            $queries[] = array( $upload_id,
                                'upload_num_pool_sources',
                                $sql );
        
            CCSync::_update_sqls($queries);
        }

        // Update the internal artists' counters for the
        // owner of the former upload as well as the owners
        // of any parents or children, this includes
        // the user's rating/ranking

        foreach( $user_ids as $user_id )
            CCSync::User(0,$user_id);


    }

    public static function Upload($upload_id)
    {
        $sql[] =<<<END
                UPDATE cc_tbl_uploads SET upload_num_remixes = 
                    (SELECT COUNT(*) FROM cc_tbl_tree WHERE tree_parent = $upload_id)
                    WHERE upload_id = $upload_id
END;
        $sql[] =<<<END
                UPDATE cc_tbl_uploads SET upload_num_sources = 
                    (SELECT COUNT(*) FROM cc_tbl_tree WHERE tree_child = $upload_id)
                    WHERE upload_id = $upload_id
END;

        $sql[] =<<<END
                UPDATE cc_tbl_uploads SET upload_num_pool_remixes = 
                    (
                        SELECT COUNT(*) FROM cc_tbl_pool_tree 
                        JOIN cc_tbl_pool_item ON pool_tree_pool_child
                        WHERE (pool_tree_parent = $upload_id) AND (pool_item_approved = 1)
                    )
                    WHERE upload_id = $upload_id
END;

        $sql[] =<<<END
                UPDATE cc_tbl_uploads SET upload_num_pool_sources = 
                    (SELECT COUNT(*) FROM cc_tbl_pool_tree WHERE pool_tree_child = $upload_id)
                    WHERE upload_id = $upload_id
END;

        CCDatabase::Query($sql);

        CCSync::User($upload_id,0);
    }


    /**
    * Method to keep an artist's internal upload counters up to date.
    *
    * Called after any change that might affect this user.
    *
    * Must be called with EITHER an upload_id owned by this artist, OR 
    * the user_id of the artist.
    *
    * Since the number of remixes, etc. affect the overall ranking of 
    * the artist, the ranking is updated as well
    *
    * @param $upload_id integer Any file id that belongs to this artist (can be null)
    * @param $user_id   integer User id to sync (can be null if upload_id is present) 
    */
    public static function User($upload_id,$user_id)
    {
        if( empty($user_id) )
        {
            $uploads = new CCTable('cc_tbl_uploads','upload_id');
            $user_id = $uploads->QueryItemFromKey('upload_user',$upload_id);
        }

        /*
            The algorithm might be a little unfair in that
            it doesn't reward a disperment of sample usage. In
            other words, if one person samples you 20 times, then
            you are considered to have be 'remixed' 20 times. It 
            seems like it would be better to reward the artist
            that has been remixed by 20 different people. 
        */
        $sql =<<<END
            SELECT COUNT(*) as user_num_uploads, upload_user as user_id
                FROM cc_tbl_uploads
                WHERE upload_user = '$user_id'
                GROUP BY upload_user
END;

        $queries[] = array( 'user_num_uploads', $sql );

        $sql =<<<END
            SELECT COUNT(*) as user_num_remixes, upload_user as user_id
                FROM cc_tbl_uploads
                WHERE ((upload_num_sources > 0) OR (upload_num_pool_sources > 0))
                      AND upload_user = '$user_id'
                GROUP BY upload_user
END;

        $queries[] = array( 'user_num_remixes', $sql );

        $sql =<<<END
            SELECT parents.upload_id parent
            FROM cc_tbl_tree tree
            JOIN cc_tbl_uploads parents ON tree_child = parents.upload_id
            JOIN cc_tbl_uploads children ON tree_parent = children.upload_id
            where children.upload_user = '$user_id'
            group by parents.upload_id
END;

        $rows = CCDatabase::QueryRows($sql);

        $sql = 'SELECT ' . count($rows) . ' as user_num_remixed,  ' . $user_id . ' as user_id';

        $queries[] = array( 'user_num_remixed', $sql );

        //
        // Execute the queries
        //
        
        $users = new CCTable('cc_tbl_user','user_id');
        
        foreach( $queries as $query )
        {
            $row = CCDatabase::QueryRow($query[1]);
            if( empty($row) )
            {
                $row['user_id'] = $user_id;
                $row[$query[0]] = 0;
            }
            $users->Update($row);
        }
    }

    /**
    *   A new upload that hasn't sampled anything doesn't
    *   affect anything but the user uploader's counts.
    *
    */
    public static function NewUpload($upload_id)
    {
        CCSync::User($upload_id,0);
    }

    /**
    * Remixes can be re-parented when the user decides to add
    * or remove sources. ('Manage Remixes' on the menu.)
    *
    * This has to happen in two phases in the code. The 'old'
    * parents must be detached which happens in remix.php,
    * and the old parent remix counts must be subtracted 
    * which happens here in CCSync::RemixDetach.
    *
    * Once the 'new' parents are attached (many of which may
    * well be the same as the old parents) then there needs
    * to be a call to CCSync::Remix to update the counts
    * on the new parents.
    *
    * @see CCSync::Remix()
    * @see CCRemix::OnPostRemixForm()
    **/
    public static function RemixDetach($remix_id)
    {
        $sql = "SELECT tree_parent as upload_id FROM cc_tbl_tree WHERE tree_child = $remix_id";
        $parents = CCDatabase::QueryRows($sql);
        CCSync::_update_parent_syncs($parents,false);
    }

    /**
    * Remixes can be re-parented when the user decides to add
    * or remove sources. ('Manage Remixes' on the menu.)
    *
    * This has to happen in two phases in the code. The 'old'
    * parents must be detached which happens in remix.php,
    * and the old parent remix counts must be subtracted 
    * which happens in CCSync::RemixDetach.
    *
    * Once the 'new' parents are attached (many of which may
    * well be the same as the old parents) then there needs
    * to be a call here to CCSync::Remix to update the counts
    * on the new parents.
    *
    * @see CCSync::RemixDetach()
    * @see CCRemix::OnPostRemixForm()
    **/
    public static function Remix($remix_id,$parents)
    {
        $sql =<<<END
            SELECT COUNT(*) as upload_num_sources, tree_child as upload_id
               FROM cc_tbl_tree
               WHERE tree_child = '$remix_id'
               GROUP BY tree_child
END;
        $queries[] = array( $remix_id,
                            'upload_num_sources',
                            $sql );
        $sql =<<<END
            SELECT COUNT(*) as upload_num_pool_sources, pool_tree_child as upload_id
               FROM cc_tbl_pool_tree
               WHERE pool_tree_child = '$remix_id'
               GROUP BY pool_tree_child
END;

        $queries[] = array( $remix_id,
                            'upload_num_pool_sources',
                            $sql );

        CCSync::_update_sqls($queries);
        CCSync::_update_parent_syncs($parents,true);
        CCSync::User($remix_id,0);
        foreach( $parents as $p )
            CCSync::User($p['upload_id'],0);
    }


    public static function PoolSourceRemix($pool_sources)
    {
        $tree = new CCPoolTree();
        $pool_items =& CCPoolItems::GetTable();
        foreach( $pool_sources as $PS )
        {
            $where['pool_tree_pool_parent'] = $PS['pool_item_id'];
            $up['pool_item_num_remixes'] = $tree->CountRows($where);
            $up['pool_item_id'] = $PS['pool_item_id'];
            $pool_items->Update($up);
        }
    }

    /**
    * Method to call after a record has been rated.
    *
    * Calculates the chart ranking of this upload and updates the
    * overall ranking of the owner of this upload.
    *
    * @param $record array Upload's record
    * @param $ratings object CCRatings table object
    */
    public static function Ratings(&$record,&$ratings)
    {
        global $CC_GLOBALS;

        $configs =& CCConfigs::GetTable();
        $C = $configs->GetConfig('chart',CC_GLOBAL_SCOPE);
        $where['ratings_upload'] = $record['upload_id'];
        $count = $ratings->CountRows($where);
        $R2['upload_id'] = $record['upload_id'];
        $R2['upload_num_scores'] = $count;
        if( empty($C['thumbs_up']) )
        {
            $average = $ratings->QueryItem( 'AVG(ratings_score)', $where );
            $stars = (floor($average/100) << 8);
            $half  = fmod($average/100,$stars) > 0.25;
            $R2['upload_score'] = floor($average);
        }
        $uploads =& CCUploads::GetTable();
        $uploads->Update($R2);
    }

    /**
    * Internal helper that actually does the math to calcuate a ranking of 
    * either an upload or a user
    */
    public static function _calc_rank(&$C,&$R,$prefix='upload')
    {
        if( !empty($C['thumbs_up']) )
        {
            if( $prefix == 'user' )
            {
                $R['user_rank'] = CCDatabase::QueryItem(
                              "SELECT SUM(upload_num_scores) FROM cc_tbl_uploads WHERE upload_user = {$R['user_id']}");
            }
            return;
        }

        /*
            rank = (v / (v+m)) * A + (m / (v+m)) * C

            where:
            A = average for the sample
            v = number of votes for the sample
            m = minimum votes required to be listed (currently 7)
            C = the mean vote across the whole list of samples with more than m votes
        */

        $m = empty( $C['bayesian-min'] ) ? 5 : $C['bayesian-min'];

        $v = $R[$prefix . '_num_scores'];

        if( $v < $m )
        {
            $rank = 0;
        }
        else
        {
            $A = $R[$prefix . '_score'];

            $sql =<<<END
                SELECT AVG(upload_score)
                FROM cc_tbl_uploads
                WHERE upload_num_scores >= $m
END;

            $C = CCDatabase::QueryItem($sql);
            
            $rank = ($v / ($v+$m)) * $A + ($m / ($v+$m)) * $C;
        }

        $R[$prefix . '_rank'] = floor( $rank );
    }

    /**
    * Internal helper to update records in the uploads table
    */
    public static function _update_sqls($queries)
    {
        $quploads = new CCTable('cc_tbl_uploads','upload_id');
        
        // 0 - upload_id
        // 1 - column
        // 2 - sql
        foreach( $queries as $query )
        {
            $row = CCDatabase::QueryRow($query[2]);
            if( empty($row) )
            {
                $row['upload_id'] = $query[0];
                $row[$query[1]] = 0;
            }
            $quploads->Update($row);
        }
    }

    /**
    * Internal helper that counts or subtracts remix counts from the parents of a remix
    */
    public static function _update_parent_syncs($parents,$add)
    {
        $minus = $add ? '' : '- 1';
        $sql = array();
        $parent_count = count($parents);
        $parent_users = array();
        $queries = array();

        $uploads = new CCTable('cc_tbl_uploads','upload_id');

        for( $i = 0; $i < $parent_count; $i++ )
        {
            $parent_id = $parents[$i]['upload_id'];
            $sql =<<<END
                SELECT (COUNT(*) $minus) as upload_num_remixes, tree_parent as upload_id
                   FROM cc_tbl_tree
                   WHERE tree_parent = '$parent_id'
                   GROUP BY tree_parent
END;
             $queries[] = array( $parent_id,
                                 'upload_num_remixes',
                                  $sql );

            if( empty($parents[$i]['upload_user']) )
                $parent_users[] = $uploads->QueryItemFromKey('upload_user',$parent_id);
            else
                $parent_users[] = $parents[$i]['upload_user'];
        }

        CCSync::_update_sqls($queries);

        $parent_users = array_unique($parent_users);

        foreach( $parent_users as $user_id )
            CCSync::User(0,$user_id);
    }


}
?>