%loop(prompts,P)%
  <div style="display:block" class="cc_%(#P/name)% cchprompt">%text(#P/value)%
    <div class="close_prompt">%text(str_close)%</div>
  </div>
%end_loop%

<script type="text/javascript">
function kill_prompt(e)
{
    this.style.display = "none";
}
CC$$('.cchprompt').each( function(e) {
    Event.observe(e,'click',kill_prompt);
});
</script>
