
layui.define(['table', 'form'], function (exports) {
  var $ = layui.$
    , table = layui.table
    , form = layui.form;

  //轮播图广告
  table.render({
    elem: '#LAY-advert-start-list'
    , url: './view_advert_list_json?position=1' //接口
    , cols: [[
      { type: 'checkbox', fixed: 'left' }
      , { field: 'name', title: '名称', width: 200 }
      , { field: 'time', width: 100, title: '时长（秒）' }
      , {
        field: 'status', title: '状态', width: 100, templet: function (d) {
          if (d.status == 0) {
            return "禁用";
          }
          if (d.status == 1) {
            return "启用";
          }
          return "错误";
        }
      }
      , {
        field: 'req_type', title: '跳转方式', width: 100, templet: function (d) {
          if (d.req_type == 1) {
            return "站内视频";
          }
          if (d.req_type == 2) {
            return "内部浏览器";
          }
          if (d.req_type == 3) {
            return "外部应用";
          }
          if (d.req_type == 4) {
            return "内部富文本";
          }
          if (d.req_type == 5) {
            return "广告代码";
          }
          return "错误";
        }
      }
      , { field: 'start_time', width: 200, title: '生效时间' }
      , { field: 'end_time', width: 200, title: '截止时间' }
      , { title: '操作', Width: 150, align: 'center', fixed: 'right', toolbar: '#table-advert-start-list' }
    ]]
    , page: true
    , limit: 10
    , limits: [10, 15, 20, 25, 30]
    , text: {
      none: '暂无相关数据'
    }
  });

  //监听工具条
  table.on('tool(LAY-advert-start-list)', function (obj) {
    var data = obj.data;
    if (obj.event === 'del') {
      layer.confirm('确定删除此广告？', function (index) {
        $.ajax({
          url: "advert_form_delete",
          type: 'post',
          data: {
            ids: data.id
          },
          success: function () {
            layer.msg("成功");
            table.reload('LAY-advert-start-list'); //重载表格
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
        , title: '编辑广告'
        , content: 'view_advert_form?id=' + data.id + '&key=start'
        , maxmin: true
        , area: ['100%', '100%']
        , btn: ['确定', '取消']
        , yes: function (index, layero) {
          var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
          submit.click();
        }
      });
    }
  });

  exports('advert_start', {})
});