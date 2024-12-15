
layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  table.render({
    elem: '#LAY-update-list'
    , url: './view_update_list_json' //接口
    , cols: [[

      { field: 'user_name', title: '用户' }
      ,{ field: 'vod_name', title: '片名' }
      ,{ field: 'update_time', title: '更新时间' }
      , { field: 'times', title: '催更次数' }
      , { title: '操作',  align: 'center', fixed: 'right', templet: function (d) {
         return '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i>删除</a>';

        } }
    ]]
    , page: true
    , limit: 10
    , limits: [10, 15, 20, 25, 30]
    , text: {
      none: '暂无相关数据'
    }
  });

  //监听工具条
  table.on('tool(LAY-update-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此数据吗？', function (index) {
        $.ajax({
          url: "update_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-update-list'); //重载表格
            layer.close(index); //再执行关闭
          },
          error: function (e) {
            layer.msg("失败")
          }
        })

      });
    } else if (obj.event === 'disbale') {
      $.ajax({
        url: "update_form_status_enable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-update-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    }else if (obj.event === 'enable') {
      $.ajax({
        url: "update_form_status_disable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-update-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    }

  });

  exports('update', {})
});
