<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template user_tags -->
%call('tag_filter')%
<script>
new ccTagFilter( { url: home_url + 'browse' + q + 'user_tags=%(user_tags_user)%', 
                   target_url: home_url + 'people/%(user_tags_user)%/',
                   tags: '%(user_tags_tag)%' } );

</script>
