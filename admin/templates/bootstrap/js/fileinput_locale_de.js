/*!
 * FileInput German Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 */
(function ($) {
    'use strict';

    $.fn.fileinputLocales['de'] = {
        fileSingle: 'Datei',
        filePlural: 'Dateien',
        browseLabel: 'Ausw&auml;hlen&hellip;',
        removeLabel: 'L&ouml;schen',
        removeTitle: 'Ausgew&auml;hlte l&ouml;schen',
        cancelLabel: 'Laden',
        cancelTitle: 'Hochladen abbrechen',
        uploadLabel: 'Hochladen',
        uploadTitle: 'Hochladen der ausgew&auml;hlten Dateien',
        msgSizeTooLarge: 'Datei "{name}" (<b>{size} KB</b>) &uuml;berschreitet maximal zul&auml;ssige Upload-Gr&ouml;&szlig;e von <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Sie m&uuml;ssen mindestens <b>{n}</b> {files} zum Hochladen ausw&auml;hlen. Bitte versuchen es erneut!',
        msgFilesTooMany: 'Anzahl der Dateien f&uuml;r den Upload ausgew&auml;hlt <b>({n})</b> &uuml;berschreitet maximal zul&auml;ssige Grenze von <b>{m}</b> St&uuml;ck.',
        msgFileNotFound: 'Datei "{name}" wurde nicht gefunden!',
        msgFileSecured: 'Sicherheitseinstellungen verhindern das Lesen der Datei "{name}".',
        msgFileNotReadable: 'Die Datei "{name}" ist nicht lesbar.',
        msgFilePreviewAborted: 'Dateivorschau abgebrochen f&uuml;r "{name}".',
        msgFilePreviewError: 'Beim Lesen der Datei "{name}" ein Fehler aufgetreten.',
        msgInvalidFileType: 'Ung&uuml;ltiger Typ f&uuml;r Datei "{name}". Nur Dateien der Typen "{types}" werden unterst&uuml;tzt.',
        msgInvalidFileExtension: 'Ung&uuml;ltige Erweiterung f&uuml;r Datei "{name}". Nur Dateien mit der Endung "{extensions}" werden unterst&uuml;tzt.',
        msgValidationError: 'Fehler beim Hochladen',
        msgLoading: 'Lade Datei {index} von {files} hoch&hellip;',
        msgProgress: 'Datei {index} von {files} - {name} - zu {percent}% fertiggestellt.',
        msgSelected: '{n} {files} ausgew&auml;hlt',
        msgFoldersNotAllowed: 'Drag & Drop funktioniert nur bei Dateien! {n} Ordner &uuml;bersprungen.',
        msgImageWidthSmall: 'Breite der Bilddatei "{name}" muss mindestens {size} px betragen.',
        msgImageHeightSmall: 'H&ouml;he der Bilddatei "{name}" muss mindestens {size} px betragen.',
        msgImageWidthLarge: 'Breite der Bilddatei "{name}" nicht &uuml;berschreiten {size} px.',
        msgImageHeightLarge: 'H&ouml;he der Bilddatei "{name}" nicht &uuml;berschreiten {size} px.',
        msgImageResizeError: 'Konnte Bildabmessungen nicht &auml;ndern.',
        msgImageResizeException: 'Fehler beim &Auml;ndern der Gr&ouml;&szlig;e des Bildes.<pre>{errors}</pre>',
        dropZoneTitle: 'Dateien hierher ziehen &hellip;'
    };
})(window.jQuery);