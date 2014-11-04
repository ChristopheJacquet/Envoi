$(function() {
    $(".closedsection").hide();
    
    $(".coords_etudiant input[type=email]").focusout(function() {
        var email = $(this).val();
        if(email == "") return;
        var emailfield = $(this);
        var nom = $(this).next("input");
        var prenom = nom.next("input");
        $.ajax("ajax.php", {
            data: {
                method: "is_valid_email",
                email: email
            },
            dataType: "json",
            success: function(data) {
                //console.log(data);
                if(data["valid"] == 1) {
                    if("nom" in data && "prenom" in data) {
                        if(nom.val() == "") nom.val(data["nom"]);
                        if(prenom.val() == "") prenom.val(data["prenom"]);
                    }
                    emailfield.removeClass("coords_email_nok");
                    emailfield.addClass("coords_email_ok");
                } else {
                    emailfield.removeClass("coords_email_ok");
                    emailfield.addClass("coords_email_nok");
                }
            }
        });
    });
    
    $(".coords_etudiant input[type=email]").on("input", function() {
       $(this).removeClass("coords_email_ok"); 
       $(this).removeClass("coords_email_nok");
    });
    
    $("form#multisuppr > h2.annee").each(function() {
       $(this).append(' <a href="#" onclick="selectYear(this)" class="selectYear">[Tout inverser]</a>'); 
    });
});


function selectYear(elt) {
    $(elt).parent().next("ul").find("input[type=checkbox]").prop("checked", function(i, val) { return !val; });
    return false;
}