(function ($) {

    jQuery(document).ready(function ($) {
        $(".update-status-btn").click(function () {
            const caseId = $(this).data("case-id");
            $("#status-update-form").show();
            $("#file-upload-form").hide();
            $("#update_case_id").val(caseId);
        });

        $(".upload-file-btn").click(function () {
            const caseId = $(this).data("case-id");
            $("#file-upload-form").show();
            $("#status-update-form").hide();
            $("#upload_case_id").val(caseId);
        });
    });

}(jQuery));
