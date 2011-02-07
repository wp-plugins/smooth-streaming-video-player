/// <reference path="jquery-1.4.1.js"/>

/**
*
* Gestion du metabox Smooth Streaming Movie.
* Dépendance: jquery.js.
*
* Author: Agence Adenova (http://www.adenova.fr)
*
*/

var SvpMetaboxManager = new Object || SvpMetaboxManager;

// Méthode de chargement de la liste des vidéos
SvpMetaboxManager.Load = function () {

    jQuery(document).ready(function ($) {

        // Effectue le chargement de la liste des vidéos en Ajax
        var data = {
            action: "get_movies",
            postid: SvpMetaboxManager.postID,
            _ajax_nonce: SvpMetaboxManager._ajax_nonce_get_movies
        };

        jQuery.post(ajaxurl, data, function (response) {
            $("#svp-movies-items").html(response);

            // Initialise le gestionnaire de sélection 
            // quand le chargement de la liste est terminé
            SvpMetaboxManager.Select();
        });


        // Gère les cases à cocher pour les options de configuration locale d'une vidéo
        $("#svp-movie-current-options input[type=checkbox]").bind("click", function (event) {
            var current = $(event.currentTarget).get(0);
            var value = $(current).next().val();
            (value == "on") ? value = "off" : value = "on";
            $(current).next().val(value);
        });

    });

}

// Méthode de gestion de la sélection de la vidéo
SvpMetaboxManager.Select = function () {

    jQuery(document).ready(function ($) {

        // Vérifie que le conteneur de vidéos existe et qu'au moins une vidéo est présente
        if ($("#svp-movies-items").length == 0 || $("#svp-movies-items li").length == 0)
            return;

        $("#svp-movies-items li").bind("click", function (event) {
            var selected = false;
            var current = $(event.currentTarget).get(0);

            if ($(current).hasClass("selected") == false
                && $(current).hasClass("unselected") == false)
                return;

            if ($(current).hasClass("selected") == true)
                selected = true;

            $("#svp-movies-items li").each(function (index) {
                $(this).removeClass("selected");
                $(this).addClass("unselected");
            });

            if (current.tagName.toLowerCase() == "li") {

                // Modifie la classe CSS de l'élément sélectionné
                if (selected == false) {
                    $(current).removeClass("unselected");
                    $(current).addClass("selected");
                }
                else {
                    $(current).removeClass("selected");
                    $(current).addClass("unselected");
                }

                // Place le nom de la vidéo dans le champ caché
                if (selected == false)
                    $("#svp-selected-movie").val($(current).text());
                else
                    $("#svp-selected-movie").val("");

                // Met à jour l'information en base de données
                if ($("#svp-selected-movie").val() != "") { // Ajout ou mise à jour
                    var data = {
                        action: "add_movie_to_post",
                        postid: SvpMetaboxManager.postID,
                        filename: $("#svp-selected-movie").val(),
                        _ajax_nonce: SvpMetaboxManager._ajax_nonce_add_movie_to_post
                    };
                }
                else { // Suppression
                    var data = {
                        action: "delete_post_movie",
                        postid: SvpMetaboxManager.postID,
                        _ajax_nonce: SvpMetaboxManager._ajax_nonce_delete_post_movie
                    };
                }

                jQuery.post(ajaxurl, data, function (response) {
                    var error = 0;

                    if (response == 0) { // Erreur

                        // Supprime l'affichage de la vidéo sélectionnée
                        $(current).removeClass("selected");
                        $(current).addClass("unselected");

                        // Indique une erreur
                        error = 2;
                    }

                    if (response > 1)
                        error = response;

                    // Change le message avec un effet d'animation pour mettre en avant le changement
                    SvpMetaboxManager.Show(error, "movie");
                });
            }
        });

    });
}

// Méthode de mise à jour des données de la vidéo associée à l'article
SvpMetaboxManager.Save = function () {
    jQuery(document).ready(function ($) {        
        var data = {
            action: "save_movie_to_post",
            postid: SvpMetaboxManager.postID,
            width: $("#svp_player_width").val(),
            height: $("#svp_player_height").val(),
            _ajax_nonce: SvpMetaboxManager._ajax_nonce_save_movie_to_post
        };

        jQuery.post(ajaxurl, data, function (response) {
            var error = 0;

            if (response == 0) // Erreur
                error = 2;

            if (response > 1)
                error = response;

            // Change le message avec un effet d'animation pour mettre en avant le changement
            SvpMetaboxManager.Show(error, "options");
        });

    });
}

// Méthode de gestion du message
// Param error integer numéro d'erreur
// Param action string type de l'action
SvpMetaboxManager.Show = function (error, action) {

    jQuery(document).ready(function ($) {
        var empty = false;

        var msg = $("#svp-movie-current p").get(0);

        if ($("#svp-selected-movie").val() == "") { // Pas de vidéo sélectionnée
            $(msg).html(SvpMetaboxManager.messages[0]);
            empty = true;
        }
        else if (error > 0) { // Error > 0, vidéo sélectionnée et erreur retournée
            $(msg).html(SvpMetaboxManager.messages[error]);
            if (error == 2)
                $("#svp-selected-movie").val("");
        }
        else { // Error == 0, vidéo sélectionnée et pas d'erreur retournée
            if (action != "options") { // Action de sélection d'une vidéo
                var selected = "\u00ab\u00a0" + $("#svp-selected-movie").val() + "\u00a0\u00bb";
                $(msg).html(SvpMetaboxManager.messages[1].replace("[movie]", selected));
            }
            else // Action de mise à jour des options
                $(msg).html(SvpMetaboxManager.messages[4]);
        }

        // Animation pour indiquer un changement
        var color = "#a4d3ef";
        if (empty == true || error > 0) { // Erreur (fond rouge)
            color = "#efa4ae";
            if (action != "options") // Action de sélection d'une vidéo
                $("#svp-movie-current-options").hide();
        }
        else // Réussite (fond bleu)
            $("#svp-movie-current-options").show();

        $(msg).animate({ backgroundColor: color }, 300);
        $(msg).animate({ backgroundColor: "#ffffe0" }, 250);

    });

}