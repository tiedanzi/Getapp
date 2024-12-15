
layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  table.render({
    elem: '#LAY-user-list'
    , url: './view_user_list_json' //接口
    , cols: [[

      { field: 'user_name', title: '用户' }
      , {
        field: 'status', title: '状态', templet: function (d) {
          if (d.status == 1) {
            return "启用";
          }
          if (d.status == 0) {
            return "禁用";
          }
          return "启用";
        }
      }
      , { field: 'create_time', title: '时间' }
      , { title: '操作', align: 'center', fixed: 'right', templet: function (d) {
          if (d.status == 1) {
            return '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="disbale"><i class="layui-icon layui-icon-edit"></i>禁用</a>' +
                '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i>删除</a>';
          }else if (d.status == 0) {
            return '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="enable"><i class="layui-icon layui-icon-edit"></i>启用</a>' +
                '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i>删除</a>';
          }

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
  table.on('tool(LAY-user-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此数据吗？', function (index) {
        $.ajax({
          url: "user_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-user-list'); //重载表格
            layer.close(index); //再执行关闭
          },
          error: function (e) {
            layer.msg("失败")
          }
        })

      });
    } else if (obj.event === 'disbale') {
      $.ajax({
        url: "user_form_status_enable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-user-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    }else if (obj.event === 'enable') {
      $.ajax({
        url: "user_form_status_disable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-user-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    }

  });

  exports('user', {})
});
