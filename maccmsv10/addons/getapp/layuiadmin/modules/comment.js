
layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  table.render({
    elem: '#LAY-comment-list'
    , url: './view_comment_list_json' //接口
    , cols: [[
      { type: 'checkbox', fixed: 'left' }
      , { field: 'user_name', title: '用户', width: 150 }
      , { field: 'vod_name', title: '视频', width: 200 }
      , { field: 'comment', title: '评论内容', width: 400 }
      , {
        field: 'status', title: '状态', width: 100, templet: function (d) {
          if (d.status == 2) {
            return "审核拒绝";
          }
          if (d.status == 1) {
            return "审核通过";
          }
          return "未审核";
        }
      }
      , { field: 'comment_report', title: '被举报次数', width: 200 }
      , { field: 'create_time', width: 200, title: '评论时间' }
      , { title: '操作', Width: 150, align: 'center', fixed: 'right', templet: function (d) {
          if (d.status == 0) {
            return '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="tongguo"><i class="layui-icon layui-icon-edit"></i>通过</a>\n' +
                '          <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="jujue"><i class="layui-icon layui-icon-edit"></i>拒绝</a>' +
                '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i>删除</a>';
          }else {
            return '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i>删除</a>'
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
  table.on('tool(LAY-comment-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此评论吗？', function (index) {
        $.ajax({
          url: "comment_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-comment-list'); //重载表格
            layer.close(index); //再执行关闭
          },
          error: function (e) {
            layer.msg("失败")
          }
        })

      });
    } else if (obj.event === 'tongguo') {
      $.ajax({
        url: "comment_form_status_enable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-comment-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    }else if (obj.event === 'jujue') {
      $.ajax({
        url: "comment_form_status_disable",
        type: 'post',
        data: {
          ids: data.id
        },
        success: function () {
          layer.msg("成功");
          table.reload('LAY-comment-list'); //重载表格
          layer.close(index); //再执行关闭
        },
        error: function (e) {
          layer.msg("失败")
        }
      })
    }

  });

  exports('comment', {})
});
