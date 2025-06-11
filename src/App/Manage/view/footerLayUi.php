</div>
<script>
    if(window.self === window.top){
        // window.location='https://www.ilayuis.com/';
    }
    $(function () {
        //无权限的按钮隐藏
        var rightsFlagArr=<?php echo json_encode(\Mt\App\Manage\Helper\Layout::rightsFlagArr());?>;

        function hideRightsButton() {
            $(".rights_btn").each(function () {
                var rights_flag = $(this).attr("rights-flag");
                if (rightsFlagArr.includes("_all") === false && rightsFlagArr.includes(rights_flag) === false) {
                    $(this).hide();
                }
            });
            setTimeout(function () {
                hideRightsButton();
            },100)
        }
        hideRightsButton();
    })
</script>
<style>
    .layui-tab-brief > .layui-tab-title .layui-this{
        color:rgb(0, 150, 136)!important;
    }
</style>
</body>
</html>