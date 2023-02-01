<div id="del_acc_modal" class="modal fade">
    <div class="modal-dialog modal-confirm">
        <div class="modal-content">
            <div class="modal-header flex-column">
                <div class="icon-box">
                    <i class="fa fa-exclamation"></i>
                </div>
                <h4 class="modal-title w-100">Are you sure?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <p>Do you really want your account? <br> This process cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                <form method="POST">
                    @csrf
                    <input type="hidden" name="userid" value="{{Auth::user()->userid}}">
                    <button type="submit" class="btn btn-danger" id="confirm-del-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
