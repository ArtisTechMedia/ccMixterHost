<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template stats (Mixter version!) -->
<style>
table.statstable {
  border-width: 3px;
  border-style: solid;
  margin: 5px;
  float:left;
}
table.statstable td {
    white-space: nowrap;
}

.shead {
  padding: 2px 1px 2px 1px;
  }
</style>

<h1><?= $T->String('str_stats_title') ?></h1>
<?
    $statsdv = new CCDataView();
    $visible = "upload_published=1 AND upload_banned=0";
?>
<table class="statstable light_bg dark_border" style="float:left">
    <tr><th class="shead dark_bg light_color" colspan="3"><?= $T->String('str_stats_overall_stats') ?></th></tr>
    <tr><th></th><th></th><th><?= $T->String('str_stats_remixed') ?></th></tr>
    <tr><td><?= $T->String('str_stats_total_uploads') ?></td>
        <td>
<?
    $n = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_uploads WHERE ' .$visible);
    print $n;
?>
        </td>
        <td></td>
    </tr>
    <tr><td><?= $T->String('str_stats_remixes') ?>:</td>
        <td>
<?
    $n = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_uploads WHERE upload_num_sources > 0 AND '.$visible);
    print $n;
?>      </td>
        <td></td>
    </tr>
<? if( $GLOBALS['strings-profile'] == 'audio' ) { ?>
    <tr><td><?= $T->String('str_stats_acappellas') ?>:</td>
        <td>
<?
    $filter = $statsdv->MakeTagFilter('acappella');
    $sql = "SELECT COUNT(*) FROM cc_tbl_uploads WHERE $filter AND $visible";
    $n = CCDatabase::QueryItem($sql);
    print $n;
?>
        </td>
        <td>
<?
    $r = CCDatabase::QueryItem($sql . " AND upload_num_remixes > 0");
    print floor( ($r*100) / $n ) . '%';
?>
        </td>
    </tr>
<? } ?>
    <tr><td><?= $T->String('str_stats_samples') ?>:</td>
        <td>
<?
    $filter = $statsdv->MakeTagFilter('sample');
    $sql = "SELECT COUNT(*) FROM cc_tbl_uploads WHERE $filter AND $visible";
    $n = CCDatabase::QueryItem($sql);
    print $n;
?>
        </td>
        <td>
<?
    $r = CCDatabase::QueryItem($sql . " AND upload_num_remixes > 0");
    print floor( ($r*100) / $n ) . '%';
?>
        </td>
    </tr>
    <tr><td><?= $T->String('str_stats_fully_mixed') ?>:</td>
        <td>
<?
    $filter = $statsdv->MakeTagFilter('original');
    $sql = "SELECT COUNT(*) FROM cc_tbl_uploads WHERE $filter AND $visible";
    $n = CCDatabase::QueryItem($sql);
    print $n;
?>
        </td>
        <td>
<?
    $r = CCDatabase::QueryItem($sql . " AND upload_num_remixes > 0");
    print floor( ($r*100) / $n ) . '%';
?>
        </td>
    </tr>
</table>

<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="3"><?= $T->String('str_stats_most_sampled_artists') ?></th></tr>
    <tr><th><?= $T->String('str_stats_artist') ?></th>
        <th><?= $T->String('str_stats_sampled') ?></th></tr>
<?
    $ccp = ccl('people') . '/';
    $sql =<<<EOF
        SELECT user_num_remixed, user_real_name, 
            CONCAT('$ccp',user_name) as url 
        FROM cc_tbl_user
        WHERE user_name NOT IN 
            ('wired','admin','criminals','militiamix','fortminor','cibelle','djdolores','apollonove','vieux','djvadim','cwillits','salman')
        ORDER BY user_num_remixed DESC LIMIT 20
EOF;

    $rows = CCDatabase::QueryRows($sql);
    foreach( $rows as $R )
    {
        ?><tr><td><a class="cc_user_link" href="<?= $R['url'] ?>"><?= $R['user_real_name'] ?></a></td>
              <td><?= $R['user_num_remixed'] ?></td></tr><?
    }
?>
</table>

<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="2"><?= $T->String('str_stats_uploads_by_month') ?></th></tr>
    <tr><th><?= $T->String('str_stats_month') ?></th><th><?= $T->String('str_stats_uploads') ?></th></tr>
<?
    $sql =<<<EOF
        SELECT SUBSTRING(upload_date,1,7) as mo, COUNT(*) as cnt 
        FROM cc_tbl_uploads 
        WHERE $visible
        GROUP BY mo 
        ORDER BY mo DESC
EOF;
    $rows = CCDatabase::QueryRows($sql);
    foreach( $rows as $R )
    {
        ?><tr><td><?= $R['mo'] ?>:</td><td><?= $R['cnt'] ?></td></tr><?
    }
?>
</table>

<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="2"><?= $T->String('str_stats_signups_by_month') ?></th></tr>
    <tr><th><?= $T->String('str_stats_month') ?></th><th><?= $T->String('str_stats_signups') ?></th></tr>
<?
    $sql = "SELECT SUBSTRING(user_registered,1,7) as mo, COUNT(*) as cnt FROM cc_tbl_user GROUP BY mo ORDER BY SUBSTRING(user_registered,1,7) DESC";
    $rows = CCDatabase::QueryRows($sql);
    foreach( $rows as $R )
    {
        ?><tr><td><?= $R['mo'] ?>:</td><td><?= $R['cnt'] ?></td></tr><?
    }
    //<tr><td>2008-01:</td><td>39</td></tr>
?>
</table>

<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="2"><?= $T->String('str_stats_most_remixes') ?></th></tr>
    <tr><th><?= $T->String('str_stats_artist') ?></th><th><?= $T->String('str_stats_remixes') ?></th></tr>
<?
    $sql =<<<EOF
        SELECT user_num_remixes, user_real_name, 
            CONCAT('$ccp',user_name) as url 
        FROM cc_tbl_user
        WHERE user_name <> 'penston' AND user_name <> 'bombero'
        ORDER BY user_num_remixes DESC LIMIT 20
EOF;

    $rows = CCDatabase::QueryRows($sql);
    foreach( $rows as $R )
    {
        ?><tr><td><a class="cc_user_link" href="<?= $R['url'] ?>"><?= $R['user_real_name'] ?></a></td><td><?= $R['user_num_remixes'] ?></td></tr><?
    }
?>
</table>

<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="2"><?= $T->String('str_stats_most_picks') ?></th></tr>
    <tr><th><?= $T->String('str_stats_artist') ?></th><th><?= $T->String('str_stats_picks') ?></th></tr>
<?
    $filter = $statsdv->MakeTagFilter('editorial_pick');
    $sql =<<<EOF
        SELECT COUNT(*) as cnt, user_real_name, 
            CONCAT('$ccp',user_name) as url 
        FROM cc_tbl_uploads
        JOIN cc_tbl_user on upload_user=user_id
        WHERE $filter AND $visible
        GROUP BY upload_user
        ORDER by cnt DESC
        LIMIT 12
EOF;
    $rows = CCDatabase::QueryRows($sql);
    foreach($rows as $R )
    {
        ?><tr><td><a href="<?= $R['url']?>" class="cc_user_link"><?= $R['user_real_name']?></a></td><td><?= $R['cnt']?></td></tr><?
    }
?>
</table>

<? if( $GLOBALS['strings-profile'] == 'audio' ) { ?>
<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="3"><?= $T->String('str_stats_most_remixed_pell') ?></th></tr>
    <tr><th><?= $T->String('str_stats_name') ?></th><th><?= $T->String('str_stats_artist') ?></th><th><?= $T->String('str_stats_remixed') ?></th></tr>
<?
    $ccf = ccl('files') . '/';
    $filter = $statsdv->MakeTagFilter('acappella');
    $sql =<<<EOF
        SELECT upload_num_remixes as cnt,
            CONCAT('$ccp',user_name) as artist_url,
            CONCAT('$ccf',user_name,'/',upload_id) as file_url,
            user_real_name, SUBSTRING(upload_name,1,30) as upload_name
        FROM cc_tbl_uploads
        JOIN cc_tbl_user on upload_user=user_id
        WHERE $filter AND $visible AND user_name NOT IN 
            ('wired','admin','criminals','militiamix','fortminor','cibelle','djdolores','apollonove','vieux','djvadim','cwillits','salman')
        ORDER by upload_num_remixes DESC
        LIMIT 12
EOF;
    $rows = CCDatabase::QueryRows($sql);
    foreach($rows as $R )
    {
        ?>
        <tr><td><a class="cc_file_link" href="<?= $R['file_url']?>"><?= $R['upload_name'] ?></a></td>
            <td><a href="<?= $R['artist_url']?>" class="cc_user_link"><?= $R['user_real_name']?></a></td>
            <td><?= $R['cnt']?></td></tr><?
    }
?>

</table>
<? } ?>
<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="2"><?= $T->String('str_stats_licenses_samples') ?></th></tr>
<?
    $filter = $statsdv->MakeTagFilter('sample');
    $sql =<<<EOF
        SELECT COUNT(*) as cnt, license_name
        FROM cc_tbl_uploads
        JOIN cc_tbl_licenses ON upload_license=license_id
        WHERE $filter AND $visible
        GROUP BY upload_license
        ORDER BY cnt
EOF;
    $rows = CCDatabase::QueryRows($sql);
    foreach( $rows as $R )
    {
        ?><tr><td><?= $R['license_name'] ?></td><td><?= $R['cnt'] ?></td></tr><?
    }
?>
</table>
<? if( $GLOBALS['strings-profile'] == 'audio' ) { ?>
<table class="statstable light_bg dark_border">
    <tr><th class="shead dark_bg light_color" colspan="2"><?= $T->String('str_stats_licenses_pells') ?></th></tr>
<?
    $filter = $statsdv->MakeTagFilter('acappella');
    $sql =<<<EOF
        SELECT COUNT(*) as cnt, license_name
        FROM cc_tbl_uploads
        JOIN cc_tbl_licenses ON upload_license=license_id
        WHERE $filter AND $visible
        GROUP BY upload_license
        ORDER BY cnt
EOF;
    $rows = CCDatabase::QueryRows($sql);
    foreach( $rows as $R )
    {
        ?><tr><td><?= $R['license_name'] ?></td><td><?= $R['cnt'] ?></td></tr><?
    }
?>
</table>
<? } ?>
    