<div id="del_Event_Modal" class="modal fade">
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
                <p>Do you really want to delete this event? <br> This process cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                <form action="{{ route('deleteEvent') }}" method="POST">
                    @csrf
                    <input type="hidden" name="eventid" id="eventid" value="{{$event->eventid}}">
                    <button type="submit" class="btn btn-danger" id="confirm-del-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
