# JTL Debug

Smarty Debug auf Steroiden

## Funktionsumfang
Ausgabe von Informationen über

- Smartyvariablen
- geladene Templates
- aktive Hooks
- PHP-Fehler
- PHP-Session
- POST/GET/COOKIE/SESSION-Objekte
- Speicherverbrauch
- phpinfo()
- JTLCache
- NiceDB
- PluginProfiler

## Allgemeine Hinweise

- Sie können den Debug-Output permanent verfügbar machen oder per Option das Vorhandensein eines GET-Parameters voraussetzen.
- Falls Sie die Option *Nur bei GET-Parameter aktivieren?* auf "Ja" gestellt haben, wird die Debug-Ausgabe nur angezeigt, wenn in der URL der im Feld Name des GET-Parameters angegebene Parameter vorhanden ist. Ein Beispiel wäre http://example.com/mein-produkt?jtl-debug
In dem Fall können Sie zusätzlich die Option *In Session speichern?* aktivieren, damit Sie den Parameter nur einmalig anhängen müssen und anschließend über Ihre Session die Ausgabe aktiviert wird.

## Anzeigen des Debug-Outputs

- Drücken Sie im Frontend Ihres Shops die Tastenkombination STRG+Enter um den Debug-Output anzuzeigen
- Erneutes Drücken dieser Kombination fokussiert das Suchfeld
Escape schließt die Ausgabe
- Alternativ können Sie in den Plugin-Optionen einen Textlink zum Anzeigen/Ausblenden des Debuggers aktivieren. 
Dies ist z.B. nützlich beim Debuggen auf mobilen Geräten, die keine Keyboard-Shortcuts unterstützen.
- Klicken auf einen Eintrag in der Ausgabe markiert automatisch den Pfad ganz rechts. Sie brauchen nur noch STRG+C zu drücken, um den Pfad der Variablen zu kopieren und können ihn z.B. direkt in Ihr Template einfügen

## Suchoptionen

- Verwenden Sie $ um nach Variablennamen zu suchen. Sie können dabei auch nur Wortteile verwenden, $einstel box würde beispielsweise auch den Knoten BoxenEinstellungen.Boxen finden.
- Um nach einem exakten Begriff zu suchen, schließen Sie ihn in Anführungszeichen ein. "Einstellungen" findet wirklich nur Knoten mit der Bezeichnung oder dem Wert "Einstellungen", nicht z.B. "Boxeneinstellungen".
- Nach Werten können Sie per ="meinWert" suchen. ="Y" gibt z.B. alle Knoten aus, die den Wert Y haben.
Ohne die Anführungszeichen werden auch Teilstrings gefunden. =123 findet Knoten mit dem Wert 123, aber z.B. auch 12345.

## Eigene Inhalte debuggen

Um eigene Variablen zum Debug-Output hinzuzufügen, können Sie die Funktion *$GLOBALS['dbg']->dump($myvar, 'myvar-name')* nutzen. *$myvar* entspricht der auszugebenen Variablen. Optional können Sie auch noch einen Namen angeben.
Der Wert erscheint anschließend in einem eigenen Abschnitt der Debug-Ausgabe.

## Version
101