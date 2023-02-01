<div id="transferOwnershipModal" class="modal fade">
    <div class="modal-dialog modal-confirm">
        <div class="modal-content" style="width: 50rem; left: -10rem;">
            <div class="modal-header flex-column">
                <div class="icon-box">
                    <i class="fa fa-user"></i>
                </div>
                <h4 class="modal-title w-100"> Transfer Ownership </h4>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">

                <form method='post' id="tranferOwnershipForm" action="{{ route('transferOwnership') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="single-post-content">
                        <input type="text" class="form-controller" id="search-attendees-teste" name="search" placeholder="Search for the user.."></input>
                        <table class="events-list" style="margin-top: 2rem;">

                            <thead>
                                <tr>
                                    <th>UserID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th> </th>
                                </tr>
                            </thead>
                            <tbody id="search-attendees-response">
                            </tbody>
                        </table>


                        <input id="currentChosen" type="hidden" name="newuserid"class="input-group form-control" value="0">
                        <input id="eventid" type="hidden" name="eventid" class="input-group form-control" value="{{$event->eventid}}">


                        <button type="submit" id="submitTOwn" type="button" class="btn btn-success btn-block"> Confirm </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
