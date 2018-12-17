;(function ($, window, document, undefined) {
    $(document).ready(function () {
        var usernameField = $("input[name='username']"),
            nameField = $("input[name='name']"),
            firstnameField = $("input[name='firstname']"),
            lastnameField = $("input[name='lastname']"),
            emailField = $("input[name='email']");

        // username
        if (usernameField !== undefined) {
            usernameField.val(usernameField.val());
            nameField.val(usernameField.val());
        }

        // firstname
        if (firstnameField !== undefined) {
            firstnameField.val(firstnameField.val());
        }

        // lastname
        if (lastnameField !== undefined) {
            lastnameField.val(lastnameField.val());
        }

        // email
        if (emailField !== undefined) {
            emailField.val(emailField.val());
        }
    });
})(jQuery, window, document);

