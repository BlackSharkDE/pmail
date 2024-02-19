/**
 * Verschiedenste JavaScript-Funktionen, die auf verschiedenen Seiten benötigt werden
 */

/**
 * Eine Funktion, die das Passwort im Passwort-Feld sichtbar macht. (void - Funktion)
 * @param string Passwort-Input-ID (HTML-ID)
 */
function showPassword(passwordFieldId) {
    let x = document.getElementById(passwordFieldId);
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}

/**
 * Erstellt die Passwort-Anzeige-Checkboxen. (void - Funktion)
 * @param string Passwort-Input-ID (HTML-ID)
 */
function addShowPassword(idOfpasswordField) {
    
    //Input (Checkbox) erstellen
    let inputBox = document.createElement("input");
    inputBox.type = "checkbox";
    inputBox.onclick = () => { showPassword(idOfpasswordField); };
    inputBox.id = "passwordCheckbox_" + idOfpasswordField;

    //Input neben/nach eigentliches Element einfügen
    let e = document.getElementById(idOfpasswordField);
    e.parentNode.insertBefore(inputBox,e.nextSibling);

    //Break einfügen
    e.parentNode.insertBefore(document.createElement("br"),e.nextSibling);

    //Label für Checkbox erstellen
    let labelForBox = document.createElement("label");
    labelForBox.for = inputBox.id;
    labelForBox.innerText = "Passwort anzeigen";

    //Label neben/nach Input-Element einfügen
    e = document.getElementById(inputBox.id);
    e.parentNode.insertBefore(labelForBox,e.nextSibling);
}