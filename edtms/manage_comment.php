<?php
require_once("./DBConnection.php");
if(isset($_GET['id'])){
$qry = $conn->query("SELECT * FROM `comment_list` where comment_id = '{$_GET['id']}'");
    foreach($qry->fetchArray() as $k => $v){
        $$k = $v;
    }
}
?>
<div class="container-fluid">
    <form action="" id="comment-form">
        <input type="hidden" name="id" value="<?php echo isset($comment_id) ? $comment_id : '' ?>">
        <input type="hidden" name="task_id" value="<?php echo isset($_GET["task_id"]) ? $_GET["task_id"] : '' ?>">
        <div class="form-group">
            <label for="message" class="control-label">Comment Message</label>
            <textarea rows="4" name="message" autofocus id="message" required class="form-control form-control-sm rounded-0 summernote" data-placeholder="Write your comment here."><?php echo isset($message) ? $message : '' ?></textarea>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('#comment-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            $('#uni_modal button').attr('disabled',true)
            $('#uni_modal button[type="submit"]').text('submitting form...')
            $.ajax({
                url:'./Actions.php?a=save_comment',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
                error:err=>{
                    console.log(err)
                    _el.addClass('alert alert-danger')
                    _el.text("An error occurred.")
                    _this.prepend(_el)
                    _el.show('slow')
                     $('#uni_modal button').attr('disabled',false)
                     $('#uni_modal button[type="submit"]').text('Save')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        _el.addClass('alert alert-success')
                        $('#uni_modal').on('hide.bs.modal',function(){
                            location.reload()
                        })
                        if("<?php echo isset($comment_id) ?>" != 1)
                        _this.get(0).reset();
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                     $('#uni_modal button').attr('disabled',false)
                     $('#uni_modal button[type="submit"]').text('Save')
                }
            })
        })
    })
</script>