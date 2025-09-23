
const tour = window.driver.js.driver({
    showProgress: false,
    prevBtnText: '<i class="si-chevron-left"></i>',
    nextBtnText: '<i class="si-chevron-right"></i>',
    doneBtnText: 'Fertig',
    smoothScroll: true,
    disableActiveInteraction: true,
    steps: [
        { popover: { title: 'Hey!', description: 'Willkommen beim HHI (Helfilisten Hosting Interface)! Hier kannst du sehen, welche Aufgaben es gibt und dich direkt für Schichten eintragen.<br/><br/>Komm, ich gebe dir eine kleine Führung! <i class="si-emoji-smile"></i>' },
            onHighlightStarted: () => {
                document.querySelectorAll(".accordion-task input[type=checkbox]").forEach((e) => (e.checked = false));
            }
        },
        { element: '.accordion-task', popover: { title: 'Die Aufgaben', description: 'Hier findest du die Übersicht aller Aufgabenbereiche, z. B. Aufbau, Bars, Nachschub oder Awareness-Team. Jede Aufgabe lässt sich aufklappen, um die Schichten zu sehen.' } },
        { element: '.accordion-task .chip-occupancy', popover: { title: 'Belegungs-Status', description: 'Dieses Feld zeigt, wie stark die jeweilige Aufgabe schon besetzt ist. So siehst du sofort, wo noch Unterstützung gebraucht wird.' } },
        { element: '.accordion-shift', popover: { title: 'Eine Schicht', description: 'Innerhalb einer Aufgabe sind die Schichten nach Uhrzeiten aufgeteilt. Hier siehst du, zu welchen Zeiten noch Helfer:innen gebraucht werden.' },
            onHighlightStarted: () => {
                document.querySelector(".accordion-task input[type=checkbox]").checked = true;
            } 
        },
        { element: '.accordion-shift .chip-occupancy', popover: { title: 'Schichtbelegung', description: 'Diese Anzeige verrät dir, wie viele Personen schon für diese Schicht eingetragen sind und wie viele noch fehlen.' } },
        { element: '.accordion-shift .accordion-body', popover: { title: 'Schicht-Detailtabelle', description: 'Wenn du eine Schicht aufklappst, findest du hier die Liste der einzelnen Plätze. Jede Zeile entspricht einer Person, die sich eintragen kann.' },
            onHighlightStarted: () => {
                document.querySelector(".accordion-shift input[type=checkbox]").checked = true;
            } 
        },
        { element: '.accordion-shift .accordion-body tr:nth-child(2)', popover: { title: 'Ein Platz in der Schicht', description: 'So sieht ein freier oder belegter Platz aus. Ist er noch leer, kannst du dich hier eintragen.' } },
        { element: '.accordion-shift .accordion-body .btn', popover: { title: 'Button zum Eintragen', description: 'Das Wichtigste: Über diesen Button trägst du dich in die ausgewählte Schicht ein. Der Rest liegt jetzt an dir!' },
            onDeselected: () => {
                document.querySelector(".accordion-task input[type=checkbox]").checked = false;
                document.querySelector(".accordion-shift input[type=checkbox]").checked = false;
                window.scrollTo(0, 0);
            }
        },
    ]
});

