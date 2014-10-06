%macro(print_xlat_head)%
<? $xlats = cc_get_topic_tranlations($A['topic_id']); ?>
%if_not_null(#xlats)%
    <div class="cc_topic_text cc_topic_xlat_links med_dark_bg"><span class="hide_me_if_you_must">&nbsp;&nbsp;&nbsp;</span>
	%loop(#xlats,xlat)%
	  <a id="xlat_topic_link_%(#xlat/topic_i18n_xlat_topic)%" class="cc_topic_xlat_link light_bg" href="javascript://xlat" 
	       onclick="return cc_show_xlat('%(topic_id)%','%(#xlat/topic_i18n_xlat_topic)%',false,'%(#xlat/topic_i18n_language)%');">&nbsp;%(#xlat/topic_i18n_language)%&nbsp;</a>&nbsp;
	%end_loop%
	<a id="xlat_topic_link_%(topic_id)%" class="cc_topic_xlat_link light_bg" href="javascript://xlat" 
	   onclick="return cc_show_xlat('%(topic_id)%','%(topic_id)%',true,'%text(str_native_lang)%');" disabled="true" >&nbsp;%text(str_native_lang)%&nbsp;</a>
    </div>
%end_if%
%end_macro%
