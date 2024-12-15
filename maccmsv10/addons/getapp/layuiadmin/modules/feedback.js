
layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  table.render({
    elem: '#LAY-content-list'
    , url: './view_feedback_list_json' //接口
    , cols: [[
      { type: 'checkbox', fixed: 'left' },
      { field: 'user_name', title: '用户', width:100}
      ,{ field: 'content', title: '反馈内容', width:200 }
      , { field: 'create_time', title: '时间' , width:200}
      ,{ field: 'reply_content', title: '回复内容' , width:200}
      ,{ field: 'reply_link', title: '跳转参数' , width:200}
      , { title: '操作', width:200,  align: 'center', fixed: 'right', templet: function (d) {
         return '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i>删除</a>\n' + '<a class="layui-btn layui-btn-default layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i>回复</a>';


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
  table.on('tool(LAY-content-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此数据吗？', function (index) {
        $.ajax({
          url: "feedback_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-content-list'); //重载表格
            layer.close(index); //再执行关闭
          },
          error: function (e) {
            layer.msg("失败")
          }
        })

      });
    } else if (obj.event === 'disbale') {
      $.ajax({
        url: "feedback_form_status_enable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-content-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    }else if (obj.event === 'enable') {
      $.ajax({
        url: "feedback_form_status_disable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-content-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    } else if (obj.event === 'edit') {
      layer.open({
        type: 2
        , title: '回复'
        , content: 'view_feedback_form?id=' + data.id
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

  exports('feedback', {})
});
