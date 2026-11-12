/* Harita & Pist Takip Sistemi — arayüz davranışları (bağımlılık: yalnızca Leaflet) */
(function () {
    'use strict';

    var hareketAzalt = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var NIKSAR = [40.5855, 36.9555];

    function $(secici, kok) { return (kok || document).querySelector(secici); }
    function $$(secici, kok) { return Array.prototype.slice.call((kok || document).querySelectorAll(secici)); }
    function kucult(metin) { return (metin || '').toLocaleLowerCase('tr'); }

    /* ------------------------------------------------------------------
       1) Kaydırınca beliren öğeler
       ------------------------------------------------------------------ */
    function belirmeyiBaslat() {
        var ogeler = $$('.reveal');
        if (!('IntersectionObserver' in window) || hareketAzalt) {
            ogeler.forEach(function (o) { o.classList.add('gorundu'); });
            return;
        }
        var gozlemci = new IntersectionObserver(function (girdiler) {
            var sira = 0;
            girdiler.forEach(function (girdi) {
                if (!girdi.isIntersecting) { return; }
                girdi.target.style.setProperty('--gecikme', Math.min(sira * 70, 350) + 'ms');
                girdi.target.classList.add('gorundu');
                gozlemci.unobserve(girdi.target);
                sira++;
            });
        }, { rootMargin: '0px 0px -40px 0px', threshold: 0.08 });
        ogeler.forEach(function (o) { gozlemci.observe(o); });
    }

    /* ------------------------------------------------------------------
       2) Sayıların sıfırdan sayarak artması
       ------------------------------------------------------------------ */
    function sayaclariBaslat() {
        $$('[data-sayi]').forEach(function (el) {
            var hedef = parseInt(el.getAttribute('data-sayi'), 10) || 0;
            if (hareketAzalt || hedef === 0) { return; }
            var sure = 900, bas = null;
            el.textContent = '0';
            function adim(t) {
                if (bas === null) { bas = t; }
                var ilerleme = Math.min((t - bas) / sure, 1);
                var yumusak = 1 - Math.pow(1 - ilerleme, 3);
                el.textContent = String(Math.round(hedef * yumusak));
                if (ilerleme < 1) { requestAnimationFrame(adim); }
            }
            requestAnimationFrame(adim);
        });
    }

    /* ------------------------------------------------------------------
       3) Bildirim (toast)
       ------------------------------------------------------------------ */
    function bildirimiBaslat() {
        var toast = $('[data-toast]');
        if (!toast) { return; }
        function kapat() {
            if (toast.classList.contains('cikiyor')) { return; }
            toast.classList.add('cikiyor');
            setTimeout(function () { toast.remove(); }, hareketAzalt ? 0 : 260);
        }
        var kapatBtn = $('[data-toast-kapat]', toast);
        if (kapatBtn) { kapatBtn.addEventListener('click', kapat); }
        setTimeout(kapat, 6000);
    }

    /* ------------------------------------------------------------------
       4) Ortak harita kurulumu (Leaflet + OpenStreetMap altlık)
          Altlık, API anahtarı gerektirmeyen standart OSM karolarıdır;
          koyu görünüm CSS filtresiyle (.koyu-altlik) verilir.
       ------------------------------------------------------------------ */
    function haritaNotu(kap, metin) {
        if ($('.harita-not', kap)) { return; }
        var not = document.createElement('p');
        not.className = 'harita-not';
        not.textContent = metin;
        kap.appendChild(not);
    }

    function haritaKur(kap) {
        if (!kap) { return null; }
        if (typeof window.L === 'undefined') {
            haritaNotu(kap, 'Harita kütüphanesi yüklenemedi.');
            return null;
        }
        var merkez = (kap.getAttribute('data-merkez') || '').split(',').map(Number);
        if (merkez.length !== 2 || isNaN(merkez[0])) { merkez = NIKSAR; }
        var zoom = parseInt(kap.getAttribute('data-zoom'), 10) || 14;

        var harita = L.map(kap, { scrollWheelZoom: false, zoomControl: true }).setView(merkez, zoom);

        var hataSayisi = 0;
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            className: 'koyu-altlik',
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> katkıcıları'
        }).on('tileerror', function () {
            hataSayisi++;
            if (hataSayisi === 4) {
                haritaNotu(kap, 'Harita altlığı yüklenemedi. İnternet bağlantınızı kontrol edin; işaretler yine de gösterilir.');
            }
        }).addTo(harita);

        // Fare tekerleği ile yakınlaştırma yalnızca harita tıklanınca açılır (sayfa kaydırmayı engellemesin)
        harita.on('click focus', function () { harita.scrollWheelZoom.enable(); });
        harita.on('mouseout blur', function () { harita.scrollWheelZoom.disable(); });

        return harita;
    }

    function pinIkonu(sinif, nabiz) {
        return L.divIcon({
            className: '',
            html: '<div class="pin ' + sinif + '">' + (nabiz ? '<span class="pin-nabiz"></span>' : '') + '</div>',
            iconSize: [22, 22],
            iconAnchor: [11, 26],
            popupAnchor: [0, -26]
        });
    }

    /* ------------------------------------------------------------------
       5) Ana sayfa: kayıt haritası
       ------------------------------------------------------------------ */
    var isaretler = {};
    var anaHarita = null;

    function anaHaritayiBaslat() {
        var kap = $('#harita');
        var veriEl = $('#harita-verisi');
        if (!kap || !veriEl) { return; }
        anaHarita = haritaKur(kap);
        if (!anaHarita) { return; }

        var kayitlar = [];
        try { kayitlar = JSON.parse(veriEl.textContent || '[]'); } catch (h) { kayitlar = []; }

        var sinirlar = [];
        kayitlar.forEach(function (k) {
            var konum = [k.enlem, k.boylam];
            sinirlar.push(konum);

            // Açılır pencere içeriği DOM ile oluşturulur (kullanıcı verisi HTML olarak yorumlanmaz)
            var icerik = document.createElement('div');
            var baslik = document.createElement('strong');
            baslik.textContent = k.baslik;
            var alt = document.createElement('span');
            alt.textContent = k.kategori + ' · ' + k.durum;
            alt.style.color = 'var(--soluk)';
            var git = document.createElement('button');
            git.type = 'button';
            git.className = 'metin-buton';
            git.style.marginTop = '8px';
            git.style.paddingLeft = '0';
            git.innerHTML = '<i class="bi bi-arrow-down-right-circle" aria-hidden="true"></i> Karta git';
            git.addEventListener('click', function () { kartaGit(k.id); });
            icerik.appendChild(baslik);
            icerik.appendChild(alt);
            icerik.appendChild(document.createElement('br'));
            icerik.appendChild(git);

            isaretler[k.id] = L.marker(konum, { icon: pinIkonu(k.anahtar), title: k.baslik, keyboard: true })
                .bindPopup(icerik)
                .addTo(anaHarita);
        });

        if (sinirlar.length > 1) {
            anaHarita.fitBounds(sinirlar, { padding: [48, 48], maxZoom: 15 });
        } else if (sinirlar.length === 1) {
            anaHarita.setView(sinirlar[0], 15);
        }

        // Kartlardaki "Haritada göster" butonları
        $$('[data-konum]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-konum');
                var isaret = isaretler[id];
                if (!isaret) { return; }
                kap.scrollIntoView({ behavior: hareketAzalt ? 'auto' : 'smooth', block: 'center' });
                anaHarita.flyTo(isaret.getLatLng(), 16, { duration: hareketAzalt ? 0 : 1.1 });
                anaHarita.once('moveend', function () { isaret.openPopup(); });
            });
        });
    }

    function kartaGit(id) {
        var kart = $('.kart[data-id="' + id + '"]');
        if (!kart) { return; }
        kart.hidden = false;
        kart.scrollIntoView({ behavior: hareketAzalt ? 'auto' : 'smooth', block: 'center' });
        kart.classList.remove('yeni');
        void kart.offsetWidth; // animasyonu yeniden başlatmak için
        kart.classList.add('yeni');
    }

    /* ------------------------------------------------------------------
       6) Ana sayfa: arama ve filtreler
       ------------------------------------------------------------------ */
    function filtreleriBaslat() {
        var kartlar = $$('.liste .kart');
        if (!kartlar.length) { return; }

        var arama = $('#arama');
        var kategoriSec = $('#kategori-filtre');
        var butonlar = $$('.filtre');
        var bos = $('.filtre-bos');
        var gorunenEl = $('[data-gorunen]');
        var durum = 'hepsi';

        // Her kartın aranabilir metni (Türkçe küçük harf kurallarıyla)
        kartlar.forEach(function (k) {
            k._arama = kucult(k.textContent.replace(/\s+/g, ' '));
        });

        function uygula() {
            var metin = kucult(arama ? arama.value.trim() : '');
            var kategori = kategoriSec ? kategoriSec.value : '';
            var gorunen = 0;
            kartlar.forEach(function (k) {
                var uygun = (durum === 'hepsi' || k.getAttribute('data-durum') === durum)
                    && (!kategori || k.getAttribute('data-kategori') === kategori)
                    && (!metin || k._arama.indexOf(metin) !== -1);
                k.hidden = !uygun;
                if (uygun) {
                    gorunen++;
                    k.classList.add('gorundu');
                }
            });
            if (gorunenEl) { gorunenEl.textContent = String(gorunen); }
            if (bos) { bos.hidden = gorunen !== 0; }
        }

        butonlar.forEach(function (btn) {
            btn.addEventListener('click', function () {
                durum = btn.getAttribute('data-filtre');
                butonlar.forEach(function (b) {
                    var aktif = b === btn;
                    b.classList.toggle('aktif', aktif);
                    b.setAttribute('aria-pressed', aktif ? 'true' : 'false');
                });
                uygula();
            });
        });
        if (arama) { arama.addEventListener('input', uygula); }
        if (kategoriSec) { kategoriSec.addEventListener('change', uygula); }

        var sifirla = $('[data-filtre-sifirla]');
        if (sifirla) {
            sifirla.addEventListener('click', function () {
                if (arama) { arama.value = ''; }
                if (kategoriSec) { kategoriSec.value = ''; }
                var tumu = $('.filtre[data-filtre="hepsi"]');
                if (tumu) { tumu.click(); } else { uygula(); }
            });
        }
    }

    /* ------------------------------------------------------------------
       7) Ekle sayfası: sayaçlar, canlı önizleme, konum seçici
       ------------------------------------------------------------------ */
    function karakterSayaclari() {
        $$('[data-sayac-for]').forEach(function (sayac) {
            var alan = document.getElementById(sayac.getAttribute('data-sayac-for'));
            if (!alan) { return; }
            var max = parseInt(alan.getAttribute('maxlength'), 10) || 0;
            function guncelle() {
                var n = alan.value.length;
                sayac.textContent = n + ' / ' + max;
                sayac.classList.toggle('sinirda', max > 0 && n > max * 0.9);
            }
            alan.addEventListener('input', guncelle);
            guncelle();
        });
    }

    // Kullanıcı hatalı bir alanı düzeltmeye başlayınca o alanın hata görünümünü kaldırır
    function hataTemizleyici() {
        $$('.alan.hatali').forEach(function (alan) {
            function temizle() {
                alan.classList.remove('hatali');
                var mesaj = $('.alan-hata', alan);
                if (mesaj) { mesaj.remove(); }
                $$('[aria-invalid]', alan).forEach(function (el) {
                    el.removeAttribute('aria-invalid');
                    el.removeAttribute('aria-describedby');
                });
                alan.removeAttribute('aria-describedby');
                alan.removeEventListener('input', temizle);
                alan.removeEventListener('change', temizle);
            }
            alan.addEventListener('input', temizle);
            alan.addEventListener('change', temizle);
        });
    }

    function onizlemeyiBaslat() {
        var kart = $('[data-onizleme]');
        var form = $('#kayit-formu');
        if (!kart || !form) { return; }

        var baslik = $('#baslik'), aciklama = $('#aciklama');
        var onBaslik = $('[data-on-baslik]', kart), onAciklama = $('[data-on-aciklama]', kart);
        var onKategori = $('[data-on-kategori]', kart), onDurum = $('[data-on-durum]', kart);

        function metinleriGuncelle() {
            onBaslik.textContent = baslik.value.trim() || 'Kayıt başlığı';
            onAciklama.textContent = aciklama.value.trim() || 'Açıklama burada görünecek.';
        }

        function kategoriGuncelle() {
            var secili = $('input[name="kategori"]:checked', form);
            if (!secili) { return; }
            onKategori.className = 'kategori-rozet ' + secili.getAttribute('data-sinif');
            $('i', onKategori).className = 'bi ' + secili.getAttribute('data-ikon');
            $('span', onKategori).textContent = secili.value;
        }

        function durumGuncelle() {
            var secili = $('input[name="durum"]:checked', form);
            if (!secili) { return; }
            var anahtar = secili.getAttribute('data-anahtar');
            kart.className = 'kart durum-' + anahtar;
            onDurum.className = 'durum durum-' + anahtar;
            $('i', onDurum).className = 'bi ' + secili.getAttribute('data-ikon');
            $('span', onDurum).textContent = secili.value;
        }

        baslik.addEventListener('input', metinleriGuncelle);
        aciklama.addEventListener('input', metinleriGuncelle);
        $$('input[name="kategori"]', form).forEach(function (r) { r.addEventListener('change', kategoriGuncelle); });
        $$('input[name="durum"]', form).forEach(function (r) { r.addEventListener('change', durumGuncelle); });
    }

    function konumSeciciyiBaslat() {
        var kap = $('#konum-harita');
        if (!kap) { return; }
        var enlem = $('#enlem'), boylam = $('#boylam');
        var metin = $('[data-konum-metin]');
        var temizle = $('[data-konum-temizle]');
        var onKonum = $('[data-on-konum]');
        var harita = haritaKur(kap);
        var isaret = null;

        function yaz(latlng) {
            if (latlng) {
                var e = latlng.lat.toFixed(6), b = latlng.lng.toFixed(6);
                enlem.value = e;
                boylam.value = b;
                metin.innerHTML = '<i class="bi bi-geo-alt-fill" aria-hidden="true"></i> ';
                metin.appendChild(document.createTextNode(e + ', ' + b));
                if (onKonum) {
                    onKonum.innerHTML = '<i class="bi bi-geo-alt-fill" aria-hidden="true" style="color:var(--vurgu)"></i> Konum seçildi';
                }
            } else {
                enlem.value = '';
                boylam.value = '';
                metin.innerHTML = '<i class="bi bi-geo-alt" aria-hidden="true"></i> Konum seçilmedi';
                if (onKonum) { onKonum.innerHTML = '<i class="bi bi-geo-alt" aria-hidden="true"></i> Konum yok'; }
            }
            temizle.hidden = !latlng;
        }

        function isaretKoy(latlng) {
            if (!harita) { return; }
            if (isaret) {
                isaret.setLatLng(latlng);
            } else {
                isaret = L.marker(latlng, { icon: pinIkonu('secim', true), draggable: true }).addTo(harita);
                isaret.on('dragend', function () { yaz(isaret.getLatLng()); });
            }
            yaz(isaret.getLatLng());
        }

        // Form hatayla geri döndüyse önceki konumu göster
        var e0 = parseFloat(enlem.value), b0 = parseFloat(boylam.value);
        if (!isNaN(e0) && !isNaN(b0)) {
            if (harita) {
                isaretKoy(L.latLng(e0, b0));
                harita.setView([e0, b0], 15);
            } else {
                yaz({ lat: e0, lng: b0 });
            }
        } else {
            temizle.hidden = true;
        }

        if (harita) {
            harita.on('click', function (olay) { isaretKoy(olay.latlng); });
        }
        temizle.addEventListener('click', function () {
            if (isaret && harita) { harita.removeLayer(isaret); isaret = null; }
            yaz(null);
        });
    }

    /* ------------------------------------------------------------------
       Başlat
       ------------------------------------------------------------------ */
    belirmeyiBaslat();
    sayaclariBaslat();
    bildirimiBaslat();
    anaHaritayiBaslat();
    filtreleriBaslat();
    karakterSayaclari();
    hataTemizleyici();
    onizlemeyiBaslat();
    konumSeciciyiBaslat();
})();
