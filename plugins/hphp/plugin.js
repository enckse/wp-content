function hphpSetCounter(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value.toLocaleString('en-US');
        return true;
    }
    return false;
}

function hphpInitAndHide(id) {
    hphpInit()
    const element = document.getElementById(id);
    if (element) {
        element.style.display = 'none';
    }
}

function hphpInit() {
    hphpLoadData(null);
}

function hphpLoadData(selector) {
    if (typeof hphp_data_payload === "undefined") {
        return;
    }
    if (selector !== null) {
        if ("triggers" in hphp_data_payload) {
            if (hphp_data_payload["triggers"].includes(selector)) {
                return
            }
        }
    }
    if ("counters" in hphp_data_payload) {
        const counters = hphp_data_payload["counters"]
        for (const key in counters) {
            const value = Math.floor(counters[key]) || 0;
            hphpSetCounter(key + "_display", value);
        }
    }
}

jQuery(document).ready(function() {
    hphpLoadData("onready")
});
