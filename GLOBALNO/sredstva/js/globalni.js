// /root/GLOBALNO/sredstva/js/globalni.js v2.0
// Globalna JavaScript logika

document.addEventListener('DOMContentLoaded', function() {
    // Obdelava obvestil
    const obvestila = document.querySelectorAll('.obvestilo');
    
    obvestila.forEach(obvestilo => {
        const avtomatsko = obvestilo.dataset.avtomatsko === 'true';
        const gumbZapri = obvestilo.querySelector('.gumb-zapri-obvestilo');
        
        const zapriObvestilo = () => {
            obvestilo.style.animation = 'drsenje-izhod 0.3s ease-in forwards';
            setTimeout(() => obvestilo.remove(), 300);
        };
        
        if (gumbZapri) {
            gumbZapri.addEventListener('click', zapriObvestilo);
        }
        
        if (avtomatsko) {
            setTimeout(zapriObvestilo, 5000);
        }
    });
    
    // Mobilni meni
    const gumbMobilniMenui = document.querySelector('.gumb-mobilni-menui');
    const navigacija = document.querySelector('.glavna-navigacija');
    
    if (gumbMobilniMenui && navigacija) {
        gumbMobilniMenui.addEventListener('click', () => {
            navigacija.classList.toggle('odprt');
            // Animacija hamburger ikone
            const crtice = gumbMobilniMenui.querySelectorAll('span');
            crtice.forEach((crtica, index) => {
                crtica.style.transform = navigacija.classList.contains('odprt') 
                    ? getHamburgerTransform(index) 
                    : 'none';
            });
        });
    }
    
    // AJAX zahteve s CSRF zetonom
    const csrfZeton = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (csrfZeton && window.jQuery) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': csrfZeton
            }
        });
    }
    
    // Globalna funkcija za AJAX zahteve
    window.ajaxZahteva = function(url, nastavitve = {}) {
        const privzeteNastavitve = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfZeton
            }
        };
        
        const koncneNastavitve = { ...privzeteNastavitve, ...nastavitve };
        
        return fetch(url, koncneNastavitve)
            .then(odgovor => {
                if (!odgovor.ok) {
                    throw new Error(`HTTP napaka! Status: ${odgovor.status}`);
                }
                
                const tipVsebine = odgovor.headers.get('content-type');
                if (tipVsebine && tipVsebine.includes('application/json')) {
                    return odgovor.json();
                }
                
                return odgovor.text();
            });
    };
    
    // Preveri, ali je uporabnik prijavljen za AJAX zahteve
    window.preveriPrijavo = function() {
        return ajaxZahteva('/api/preveri-prijavo')
            .then(odgovor => odgovor.prijavljen)
            .catch(napaka => {
                console.error('Napaka pri preverjanju prijave:', napaka);
                return false;
            });
    };
    
    // AI pomocnik funkcionalnosti
    window.obdelajAIvprasanje = function(vprasanje, ponudnik = 'google') {
        const podatki = new FormData();
        podatki.append('ai_vprasanje', vprasanje);
        podatki.append('ai_ponudnik', ponudnik);
        podatki.append('csrf_zeton', csrfZeton);
        
        return ajaxZahteva('/api/ai-pomocnik', {
            method: 'POST',
            body: podatki
        });
    };
});

// Pomozne funkcije za hamburger meni
function getHamburgerTransform(index) {
    const transforms = [
        'rotate(-45deg) translate(-5px, 6px)',
        'opacity(0)',
        'rotate(45deg) translate(-5px, -6px)'
    ];
    return transforms[index] || 'none';
}

// Dodaj animacijo za izhod obvestil
const slog = document.createElement('style');
slog.textContent = `
    @keyframes drsenje-izhod {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .glavna-navigacija.odprt .navigacija-spodnji-del {
        display: flex !important;
    }
    
    @media (max-width: 768px) {
        .navigacija-spodnji-del {
            display: none;
        }
    }
    
    /* Loading indicator */
    .nalaganje {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: vrtenje 1s linear infinite;
    }
    
    @keyframes vrtenje {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(slog);