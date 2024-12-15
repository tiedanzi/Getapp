
layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  table.render({
    elem: '#LAY-notice-list'
    , url: './view_notice_list_json' //接口
    , cols: [[
      { type: 'checkbox', fixed: 'left' }
      , { field: 'title', title: '标题', width: 300 }
      , {
        field: 'is_top', title: '是否置顶', width: 100, templet: function (d) {
          if (d.is_top == 0) {
            return "否";
          }
          if (d.is_top == 1) {
            return "是";
          }
          return "否";
        }
      }
      , {
        field: 'status', title: '状态', width: 100, templet: function (d) {
          if (d.status == 0) {
            return "禁用";
          }
          if (d.status == 1) {
            return "启用";
          }
          return "禁用";
        }
      }
      , { field: 'create_time', width: 200, title: '发布时间' }
      , { field: 'sort', width: 100, title: '排序' }
      , { title: '操作', Width: 300, align: 'center', fixed: 'right', toolbar: '#table-notice-list' }
    ]]
    , page: true
    , limit: 10
    , limits: [10, 15, 20, 25, 30]
    , text: {
      none: '暂无相关数据'
    }
  });

  //监听工具条
  table.on('tool(LAY-notice-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此公告？', function (index) {
        $.ajax({
          url: "notice_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-notice-list'); //重载表格
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
        , title: '编辑公告'
        , content: 'view_notice_form?id=' + data.id
        , maxmin: true
        , area: ['100%', '100%']
        , btn: ['确定', '取消']
        , yes: function (index, layero) {
          var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
          submit.click();
        }
      });
    }else if (obj.event === 'sort') {
      layer.open({
        type: 2
        , title: '修改排序'
        , content: 'view_notice_sort?id=' + data.id
        , maxmin: true
        , area: ['300px', '200px']
        , btn: ['确定', '取消']
        , yes: function (index, layero) {
          var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
          submit.click();
        }
      });
    }else if (obj.event === 'top') {
      layer.open({
        type: 2
        , title: '修改置顶'
        , content: 'view_notice_top?id=' + data.id
        , maxmin: true
        , area: ['500px', '200px']
        , btn: ['确定', '取消']
        , yes: function (index, layero) {
          var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
          submit.click();
        }
      });
    }
    
  });

  exports('notice', {})
});