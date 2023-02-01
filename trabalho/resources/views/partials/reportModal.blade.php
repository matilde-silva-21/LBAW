<div id="reportModal" class="modal fade">
    <div class="modal-dialog modal-confirm">
        <div class="modal-content">
            <div class="modal-header flex-column">
                <div class="icon-box">
                    <i class="fa fa-user-slash"></i>
                </div>
                <h4 class="modal-title w-100"> Report Comment </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">

                <form class="row g-3 needs-validation" id="report-form" novalidate action="storeReport" method="post">
                    @csrf
                    <input type="hidden" name="comment_id" id="comment_id" value="">
                    <input type="hidden" name="event_id" id="event_id" value="">
                    <input type="hidden" name="user_id" id="user_id" value="">

                    <div>
                        <label for="validationCustom01" class="form-label"> Reason</label>

                        <select class="form-select" name="reason" id="validationCustom01" required>
                            <option selected disabled value="">Choose...</option>
                            <option> Dangerous/Illegal </option>
                            <option> Discriminatory </option>
                            <option> Misinformation </option>
                            <option> Disrespectful </option>
                            <option> Other </option>
                        </select>
                        <div class="valid-feedback">
                            Looks good!
                        </div>
                    </div>
                    <div>
                        <label for="validationCustom02" class="form-label">Explanation </label>
                        <textarea name="explanation" style="max-height:10rem;" class="form-control" id="validationCustom02" placeholder="Comment" required></textarea>
                        <div class="valid-feedback">
                            Looks good!
                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="confirm-report-btn">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" defer>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>