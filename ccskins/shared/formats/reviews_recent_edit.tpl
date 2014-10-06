<?/*
[meta]
    type = template_component
    desc = _('7 most recent reviews (UI)')
[/meta]
*/?>

<h3>%text(str_reviews_most_active)%

  <select id="cc_hot_topic_picker">
    <option value="<?= date('Y-m-d', strtotime('1 week ago')) ?>" selected="selected">%text(str_reviews_past_week)%</option>
    <option value="<?= date('Y-m-d', strtotime('1 month ago'))?>">%text(str_reviews_past_month)%</option>
    <option value="2004-01-01">%text(str_reviews_all_time)%</option>
  </select>
</h3>
<div id="cc_hot_topics_div" style="margin-bottom:1.5em;"></div>
<script src="%url(js/cc-topics.js)%" type="text/javascript" ></script>
