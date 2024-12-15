layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  //版本管理
  table.render({
    elem: '#LAY-app-version-list'
    , url: './view_version_list_json' //接口
    , cols: [[
      { type: 'checkbox', fixed: 'left' }
      , { field: 'id', width: 100, title: '版本id' }
      , { field: 'version_name', title: '版本标题', minWidth: 100 }
      , { field: 'version_code', title: '版本号' }
      , { field: 'download_url', title: '下载地址' }
      , { field: 'description', title: '版本描述', maxWidth: 150 }
      , { field: 'app_size', title: '包大小' }


      , {
        field: 'is_force', title: '强制更新',  templet: function (d) {
          if (d.is_force == 0) {
            return "否";
          }
          if (d.is_force == 1) {
            return "是";
          }
          return "否";
        }
      }
      , { field: 'browser_download_url', title: '跳转浏览器地址' }
      ,{ title: '操作', minWidth: 150, align: 'center', fixed: 'right', toolbar: '#table-version-list' }
    ]]
    , page: true
    , limit: 10
    , limits: [10, 15, 20, 25, 30]
    , text: {
      none: '暂无相关数据'
    }
  });

  //监听工具条
  table.on('tool(LAY-app-version-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此版本？', function (index) {
        $.ajax({
          url: "version_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-app-version-list'); //重载表格
            layer.close(index); //再执行关闭
          },
          error: function (e) {
            layer.msg("失败")
          }
        })

      });
    } else if (obj.event === 'edit') {
      layer.open({
        type: 2
        , title: '编辑版本'
        , content: 'view_version_form?id=' + data.id
        , maxmin: true
        , area: ['600px', '600px']
        , btn: ['确定', '取消']
        , yes: function (index, layero) {
          var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
          submit.click();
        }
      });
    }
  });

  exports('version', {})
});
