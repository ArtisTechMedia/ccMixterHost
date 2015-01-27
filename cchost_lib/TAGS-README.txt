/*

$Id: TAGS-README.txt 13856 2009-12-29 15:08:51Z fourstones $

    How tags work
    -------------
    
    This documentation is aimed at admins, query writers and
    ccHost developers, but favors none. Each should get something
    out of it.
    
    N.B. the implementation of tags in ccHost is spread around,
    much of it happens in cc-query.php, cc-tags.inc/php and
    in cc-tag-query.php
    
    There are several types of tags
    
        System tags -
            These are maintained by the 'system' and some admin activity.
            GetID3 analysis will create file format tags (like 'mp3' and
            '44k'). Some of the tags in a files 'Admin' screen are system tags
            (like license tags, 'attribution' etc.).

        Psuedo System tags -            
            The tags in the submit forms are also treated as system tags but
            are controlled by the admins. These are sometimes called
            'Submit type' indicating the type of submit form used to
            initially upload  like 'remix' and 'acappella' (the bpm module
            also uses stores its tags with these). Throughout the
            code these are referred as 'ccud' for (unknown) historical
            reasons.
            
            The edit field in an upload's Admin screen are the psuedo
            system tags for that upload.
            
        Admin tag -
            These are controlled by the admin at: admin/tag/properties
            and admin/tagcat.
            
            They strictly exist to prevent users from using tags the admins
            don't want to see in the system.  
            
        User tag -
            These are put onto uploads by the user in the Properties
            screen. On every new upload and property edit, these tags
            are filtered for System and Admin tags which are removed. They
            are also 'translated' using the tag alias table.
            
        Tags are broken up by type and stored in the upload_extras field
        (which is serialzed for storage)
            [upload_extra]
               [ccud] - Psuedo System tags
               [systags] - System tags
               [usertags] - User tags
               
        There can be additional Psuedo System tags in the [file_extra]
        field in cc_tbl_files (also serialized) for files added to
        the main record
            [file_extra]
                [ccud]
                     
        All tags for an upload and its files are combined, comma
        separated, in the upload_tags field for searching purposes.
        The field should (but doesn't always) begin and end with a
        comma (',') to make it easy to search for:
        
                WHERE upload_tags LIKE '%,remix,%'
                
        This bookending with commas is not guarenteed so often you
        will see
        
                WHERE CONCAT(',',upload_tags,',') LIKE '%,remix,%'
    
        There are 3 defines used in the tags table for the
        tag_type field:
        
            CCTT_SYSTEM - system and psuedo system tags
            CCTT_ADMIN  - admin tags
            CCTT_USER   - user tags
            
        Having the tags embedded into each upload record (twice
        actually) means there is no straight forward way to change
        the name of a tag without touching every record that uses
        that tag. 
            
    Promoting Tags From User to Admin or System
    
        The following actions will 'promote' a tag from being
        simply a user tag to one of the kind:
        
            - Used in the 'tags' field when editing submit forms
            - Used in the 'tags' field of a file's Admin screen
            - Entered into the 'Reserved tags' field of the
              global tags property screen
            - Manually change the type in 'Assign Categories'
              screen (admin/tagcat)
              
        It is important to note that when a tag is promoted,
        it will only affect future use of the tag - that is:
        users will be restricted from using those tags only
        in future uploads. (The system will also notice the
        tag promotion if the user manually edits the properties
        of an upload that already had that tag. The system
        will remove the newly promoted tag when the user
        submits the Properties form.)
        
        In order to remove the tag from existing uploads,
        admins have to run the admin/tag/reset command which
        explained in more detail below.
        
    
    Tag aliasing is how admins control normalizing tags.
    
        Aliases are a touple of possible user input and outward
        translation. These pairing are edited in the Tags
        Properties screen admin/tags/properties and can also
        be manipulated for existing tags in the Assign Tag
        Categories screen admin/tagcat
        
        For example, admin may want to normalize on
        acoustic_guitar. Therefore the alias table might have the
        following pairing:
        
            guitar_acoustic    becomes=>  acoustic_guitar
            acoustic           becomes=>  acoustic_guitar
            acoustic_giutar    becomes=>  acoustic_guitar
            
        This system can also be used to correct misspellings as
        in the last pairing.
    
        (It is planned to use the tag alias table in searching
        to provide a "did you mean?" feature for searches)
        
        Note that in all cases, the value on the right is the
        same in order to make tag searching more consistant.
        
        If the tag on the right side does not exist, it will
        be added to the system the first time the rule is
        applied.
        
        It is also possible to use multiple tags on the right
        side. Use commas to separate:
        
            acoustic_guitar     becomes=>  guitar,acoustic    
            guitar_acoustic     becomes=>  guitar,acoustic    
            acoustic            becomes=>  guitar,acoustic

        One weird side-affect of all this: If the tag on the
        right is not a user tag (e.g. it has been promoted
        somewhere down the line) then the entry on the left
        will be replaced with nothing - that is, effectively
        throwing it away. If there are multiple tags on
        the right, only the user tags will be applied to
        the upload.
        
        Alias rules will apply to future uploads or edits of
        uploads. To apply them retroactively to
        existing upload records, the admin has to run
        the reset command explained below.
        
        Alias rules only apply to User tags, even retroactively
        through the reset command. 

    Tag Pairs
    
        A ccHost system will have multiple submission types
        represented by psuedo tags (aka CCUDs).
        
        For example, for 'remix' or 'sample' the same tag
        can have different implications on each. Furthermore
        search interfaces that allow for searching by tag
        need to be able to differentiate tags that are used
        by one submission type versus the other.
        
        There is a table for maintaing these relationships
        called cc_tbl_tag_pair which keeps track of which
        tags are used with which submission type.
        
        These pairings must be maintained manually by calling
        admin/tags/reset (see below)

        N.B. As of this writing, the actual controller tags
        (like 'remix' in the example above) are buried in
        the code in cc-tags-recalc.php (see the $subtypes
        variable). There are plans to make these admin
        editable.
        
        Tag pairs do not currently show up in any ccHost
        interface, however they are exposed via the Query
        API as discussed below.
                
    Global counts vs. Tag Pair counts
    
        The total number of uploads that use a tag can
        be acheived:
        
            SELECT COUNT(*) FROM cc_tbl_uploads WHERE
                upload_tags LIKE '%,TAG_NAME_HERE,%'
                
        That count is kept up to date for each tag in tags_count
        field of cc_tbl_tags by the system for every upload,
        property edit, upload deletion, etc.
        
        There are many cases where the user interface
        wants to know the number of tags used for a specific
        submit type (Psuedo System/CCUD). For example, there
        may be 2,000 uploads tags 'hip_hop', but you want
        to know how many remixes (as opposed to samples)
        are marked 'hip_hop'.
        
        This can be calcuated:

            SELECT COUNT(*) FROM cc_tbl_uploads WHERE
                upload_tags LIKE '%,hip_hop,%' AND
                upload_tags LIKE '%,remix,%' 
        

        The pairing count is kept in the tag pair table.
        
        The pairing counts are not currently used in the
        ccHost interface but is exposed via the Query API
        as discussed below.

    Resetting the counts and pairing (admin/tag/reset)
    
        To help keep the system fresh and up to date,
        admins need to run admin/tag/reset at a regular
        interval (or after they add alias rules or
        admin tags).
        
        Running this command will:
        
            - Run all tag alias rules for all uploads
            - Remove Admin tags
            - Reset all global counts for all tags
            - Reset all pair matches and their counts
            - Remove user tags with a count of zero (e.g.
               if the tag has been aliased out of existance)

    Categories are a way to group tags.
    
        Admins can create tag categories in admin/tagcat/cats.
        These categories will then be visible in the assignment
        screen admin/tagcat.
        
        Categories are stored in the cc_tbl_tag_category
        table.
        
        The category of each tag (a tag can only be in one
        category) is stored in the tags_category field of
        cc_tbl_tags

        Tag categories are not currently used in the
        ccHost interface but is exposed via the Query API
        as discussed below.

    Assigning Categories
    
        The 'Assign Tag Categories' admin screen at
        admin/tagcat is a place where admins can almost anything
        with existing tags.
        
        Using this screen, admins can:
            - Isolate tags which have not been categorized
              and assign them to categories very quickly
            - Find a specific tag and work on its
              properties
            - Find a group of tags that have a similar
              name (e.g. all tags that contain 'piano')
            - Get a detailed overview of all the tags
              in the system, categorized or not, and
              make changes appropriate

        For each tag an admin:
            - Assign a category (default is none)
            - Promote/demote the tag's type
            - Create an alias rule for the tag
        
        These last two operations can also be done in the
        Tags Properties screen at admin/tag/properties
        but are provided on this screen to make it easier
        to do large scale tag admin duties.
        
        Since the alias rule only applies to user tags, the
        rule edit field is disabled (and ignored) when
        the form is submitted if the tag is not marked
        as User.*
        
        *this disabling feature is actually not implemented
        as of this writing but should be by the time you
        read this. emphasis on _should_ be
                
    Tags and the Query API
    
        Tags have always been a part of the Query API
        in regards to uploads but in ccHost 5.3 (SVN
        5.2) they have been enhanced to have more
        control and flexibility over tag combinations.
        In addition, in 5.3 the tags tables themselves are
        represented in the Query API with datasource=tags
        and datasource=tag_cat
        
        Combinging Tags
        
            In the original API, tags could be combined
            by using 'reqtags' and 'tags' parameters.
            All tags in 'reqtags' would be required while
            tags in the 'tags' parameter could be either
            AND'd or OR'd depending on the 'type' parameter
            which could be either 'any' or 'all'.
            
            In the case of:
            
                reqtags=remix+instrumental
                tags=hip_hop+funky+-guitar
                type=any
                
            would be interpreted as:
            
                return all uploads that have 'remix'
                AND 'instrumental' tags that also
                have either 'hip_hop' or 'funky'
                and does not have 'guitar'
                
            (The '-' exclusion sign is always AND'd with
            the rest of the expression.)
            
            This scheme however was not flexible enough
            once the concept of tags categories (a.k.a.
            groups) where introduced. For these cases
            you need to be able to request along the
            lines of categories like 'genre', 'submit
            type', 'style', 'instrument', etc.
            
            Something like:
            
                return all uploads that have
                    'hip_hop' OR 'funk'    // category: genre
                        AND
                    'east_coast'           // category: style
                        AND
                    'remix' OR 'original'  // category: submit_type
                        AND
                    'happy' OR 'dancable'  // category: mood
                        AND
                    orchestral' AND 'brass' // category: instrument
                        AND
                    NOT 'guitar'
                    
            For even more flexibility, you should be able to
            mix and match one tag in one category to another. The
            above request could turn out to start out with:
            
                    'hip_hop' AND 'east_count'  // genre+style
                        OR
                    'funk' AND 'james_brown'    // genre+style
                        AND
                     ....
            
            In the enhanced version tags are grouped by parenthesis 
            in the new 'tagexp' parameter with new combining
            operators:
            
                    tagexp=(hip_hop|funk)*east_coast
                    
            The second variation would require nested groups:
            
                    tagexp=((hip_hop*east_coast)|(funk*james_brown))
            
            Note that '*' indicates all tags and groups are required,
            '|' indicates only one in that group need be present.
            
        
        Querying for Tags
        
            This section is about when you want to query
            for tags (as opposed to upload or users or topics).
            
            As of this writing there are 2 relevant dataviews
            that can be used to retrieve the tag information
            programmatically or in a template. There is no
            such template currently in ccHost. (The /tags
            command was implemented before the Query API
            was extended to include tags.) Because of this,
            formats that typically are used to display
            query results like 'html' and 'page' will
            not work.
            
            Programmable formats such as 'js' and 'json' and
            structed formats like 'xml' and 'csv' will work.
            
            N.B. 'json' format is NOT suggested because FireFox
            (for one) will not accept enough data in the HEAD
            to be useful. Use the 'js' format and eval() the
            results instead in your javascript code

            There is however a test template you can use
            while getting the hang of things: tag_list
            Don't use the default 'page' format because
            it will limit the results to just 15. Use
            the 'html' format instead:
            
              api/query?t=tag_list&f=html&cat=genre
              
            
            To retrieve all tag categories:
            
                dataview=tag_cats&f=js
                
            This will return the display name and category id
            for each admin created categories.
            
            To see the specific information for just one category
            use the 'cat' parameter with the id. For example,
            assuming there is a 'genre' category:
            
                dataview=tag_cats&f=js&cat=genre
                
            To retrieve all tags in the system (paging is _strongly_
            recommended)
            
                dataview=tags&f=js&offset=0&limit=100
                
            these can be order by 'name' or 'count' either ascending
            or decending. (Default is count, desc)

                dataview=tags&f=js&offset=0&limit=100&ord=name
                
            The results are
                tags_tag
                tags_count
                tag_category
                
            (There no 's' in the last field name - sorry)
            
            To specify a category use the 'cat' parameter with
            a tag_category_id retrieved from the first query in
            this section. For example, to see all genre tags:
            
                dataview=tags&f=js&cat=genre

            To see only the tags that are used for a specific
            pairing:

                dataview=tags&f=js&pair=remix
            
            When you don't specify a paring the count represents
            the usage for that tag across the system. With a
            pairing the count reflects the cross section.
        
*/
