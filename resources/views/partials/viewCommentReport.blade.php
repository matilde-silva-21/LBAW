<div id="view_comment_report" class="modal fade">
    <div class="modal-dialog modal-confirm">
        <div class="modal-content">
            <div class="modal-header flex-column">

            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <div class="comment-container">
                    <div class="div1">
                        <img src="" alt="Profile image">
                    </div>
                    <div class="div2">
                        <h4 class="name"></h4>
                    </div>
                    <div class="div3">
                        <div>
                            <p class="text"></p>
                        </div>
                    </div>

                    <ul>
                        <li class="rep_date"></li>
                        <li class="rep_reason"></li>
                        <li class="rep_description"></li>

                    </ul>
                </div>

                <form method="post">
                    @csrf
                    <input type="hidden" name="__rep_user_id" value="0">
                    <button type="submit" class="btn btn-danger" id="confirm-ban-btn">Block User </button>
                </form>
            </div>
        </div>
    </div>
</div>
