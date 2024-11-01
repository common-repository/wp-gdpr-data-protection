! function(t) {
    "use strict";
    t(function() {
        function a(t) {
            t = (t = t.replace(/^\s+|\s+$/g, "")).toLowerCase();
            for (var a = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;", e = 0, n = a.length; e < n; e++) t = t.replace(new RegExp(a.charAt(e), "g"), "aaaaeeeeiiiioooouuuunc------".charAt(e));
            return t = t.replace(/[^a-z0-9 -]/g, "").replace(/\s+/g, "-").replace(/-+/g, "-")
        }
        t(document).on("click", "#tabs .notice-dismiss", function(a) {
            a.preventDefault(), t(this).closest(".postbox").remove()
        }), t(document).on("click", ".wooenc-request-table .wooenc-review", function(a) {
            a.preventDefault();
            var e = t(this).data("index");
            t("tr[data-index=" + e + "] div").slideToggle()
        }), t(document).on("click", ".wooenc .nav-tab-wrapper a", function(a) {
            var e = t(this).attr("href");
            if (e = e.replace("#", ""), t(this).addClass("nav-tab-active"), t(this).siblings().removeClass("nav-tab-active"), t(".wooenc .tab").addClass("hidden"), t(".wooenc .tab[data-id=" + e + "]").removeClass("hidden"), -1 !== location.search.indexOf("page=wooenc-settings")) {
                var n = t('.wooenc form input[name="_wp_http_referer"]'),
                    i = n.val().split("#")[0];
                n.val(i + "#" + e)
            }
        });
        var e = window.location.hash;
        if (e) {
            if (t('.wooenc .nav-tab-wrapper a[href="' + e + '"]').addClass("nav-tab-active"), t('.wooenc .tab[data-id="' + e.replace("#", "") + '"]').removeClass("hidden"), -1 !== location.search.indexOf("page=wooenc-settings")) {
                var n = t('.wooenc form input[name="_wp_http_referer"]'),
                    i = n.val().split("#")[0];
                n.val(i + e)
            }
        } else t(".wooenc .nav-tab-wrapper a:eq(0)").addClass("nav-tab-active"), t(".wooenc .tab:eq(0)").removeClass("hidden");
        t(document).on("change", ".wooenc-reassign", function() {
            0 != t(this).val() ? (t(this).closest("tr").find("td:last .button-primary").attr("disabled", !1), t(this).closest("tr").find('td:last input[name="reassign_to"]').val(t(this).val())) : (t(this).closest("tr").find("td:last .button-primary").attr("disabled", !0), t(this).closest("tr").find('td:last input[name="reassign_to"]').val(""))
        })
    })
}(jQuery);