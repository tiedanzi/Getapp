<div id="content" style="display: none;">element</div>
<script src="/static/js/jquery.js"></script>
<script>
  var iframe;
  var element = $("#content")[0];
  $("iframe", window.parent.document).each(function() {
    if (element.ownerDocument === this.contentWindow.document) {
      iframe = this;
    }
    return !iframe;
  });
  var layId = $(iframe).attr("lay-id");
  window.open(parent.ADMIN_PATH + "/admin/getapp");
  parent.$('li[lay-id="'+layId+'"]').find(".layui-tab-close").click();
</script>
