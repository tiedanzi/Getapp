layui.define(['table', 'form'], function (exports) {
    var $ = layui.$
        , table = layui.table
        , form = layui.form;

    //版本管理
    table.render({
        elem: '#LAY-player-parse-list'
        , url: './view_player_parse_list_json'
        , cols: [[
            {field: 'sort', title: '排序', width: 100}
            , {field: 'from', title: '播放器编码', width: 150}
            , {field: 'show', title: '播放器名称', width: 200}
            , {
                field: 'parse_api', title: 'APP播放源(点击编辑)', minWidth: 150, templet: function (d) {
                    var str = "";

                    if (d.parse_api != null) {
                        for (var i = 0; i < d.parse_api.length; i++) {
                            if (d.parse_api[i].app_is_show == 1) {
                                str += '<a class="layui-btn" lay-event="edit" data-id="' + d.parse_api[i].id + '" ><i class="layui-icon layui-icon-star-fill"></i>显示 | ' + d.parse_api[i].player_name + ' - ' + d.parse_api[i].jx_type_name + '</a><a lay-event="del" data-id="' + d.parse_api[i].id + '"><i  style="font-size:26px; margin: 10px; color: red;cursor: pointer" class="layui-icon layui-icon-delete"></i></a><div style="height: 5px"></div>';
                            } else {
                                str += '<a class="layui-btn layui-btn-gray" lay-event="edit" data-id="' + d.parse_api[i].id + '" ><i class="layui-icon layui-icon-star-fill"></i>隐藏 | ' + d.parse_api[i].player_name + ' - ' + d.parse_api[i].jx_type_name + '</a><a lay-event="del" data-id="' + d.parse_api[i].id + '"><i  style="font-size:26px; margin: 10px; color: red;cursor: pointer" class="layui-icon layui-icon-delete"></i></a><div style="height: 2px"></div>';

                            }
                        }

                    }
                    return str;
                }
            }

            , {title: '操作', width: 100, align: 'center', fixed: 'right', toolbar: '#table-player-parse-list'}
        ]]
        , page: false
        , text: {
            none: '暂无相关数据'
        }
    });

    //监听工具条
    table.on('tool(LAY-player-parse-list)', function (obj) {
        var data = obj.data;
        if (obj.event === 'add') {
            layer.open({
                type: 2
                , title: '添加APP播放源'
                , content: 'view_player_parse_form?id=' + data.from
                , maxmin: true
                , area: ['550px', '600px']
                , btn: ['确定', '取消']
                , yes: function (index, layero) {
                    var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
                    submit.click();
                }
            });
        }

        if (obj.event === 'edit') {
            var key = $(this).data('id');
            layer.open({
                type: 2
                , title: '修改APP播放源'
                , content: 'view_player_parse_form?id=' + data.from + "&key=" + key
                , maxmin: true
                , area: ['680px', '600px']
                , btn: ['确定', '取消']
                , yes: function (index, layero) {
                    var submit = layero.find('iframe').contents().find("#layuiadmin-app-form-submit");
                    submit.click();
                    setTimeout(function () {
                        table.reload('LAY-player-parse-list'); //重载表格
                    }, 2000)
                }
            });
        }

        if (obj.event === 'del') {
            var key = $(this).data('id');
            layer.confirm('确定删除此数据吗？', function (index) {
                $.ajax({
                    url: 'player_parse_delete',
                    type: 'post',
                    data: {
                        id: data.from,
                        key: key
                    },
                    success: function () {
                        layer.msg("成功");
                        setTimeout(function () {
                            table.reload('LAY-player-parse-list'); //重载表格
                            layer.close(index); //再执行关闭
                        }, 2000)

                    },
                    error: function (e) {
                        layer.msg("失败")
                    }
                })

            });
        }

    });

    exports('player-parse', {})
});
