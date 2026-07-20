DF Collection – README

1. Cel modułu

dfcollection to własny moduł PrestaShop odpowiedzialny za rozbudowaną sekcję kolekcji na stronie głównej. Jego zadaniem jest prezentowanie wybranych kolekcji mebli w formie dużego, interaktywnego bloku z:

zakładkami kolekcji,

sticky barem kolekcji pojawiającym się podczas scrolla,

dużym zdjęciem kolekcji,

opcjonalnym trybem compare dla zdjęcia,

opcjonalnym zdjęciem aranżacji w overlayu,

krótkim opisem kolekcji,

badge / cechami kolekcji,

zakresem kolekcji,

polecanym produktem,

opcjonalnym countdownem opartym o produkt polecany,

sliderem produktów z wybranej kategorii,

informacją o najniższej cenie w kolekcji,

informacją o darmowej dostawie od,

sekcją „Najczęściej kupowane razem”,

AJAX-owym przełączaniem kolekcji bez przeładowania strony.

Moduł został zbudowany tak, aby działał jako mocny blok sprzedażowy i wizualny na homepage.

2. Co dokładnie robi moduł na froncie

Na stronie głównej moduł renderuje sekcję kolekcji. Użytkownik może:

przełączać się między kolekcjami,

korzystać ze sticky bara kolekcji podczas scrollowania strony,

oglądać duże zdjęcie kolekcji,

oglądać wersję compare zdjęcia, jeśli została ustawiona,

zobaczyć produkt polecany dla danej kolekcji,

zobaczyć countdown produktu polecanego, jeśli został włączony,

przejrzeć slider produktów z tej kategorii,

zobaczyć najniższą cenę w danej kolekcji,

zobaczyć próg darmowej dostawy dla tej kolekcji,

przeczytać krótki opis kolekcji,

zobaczyć zakres kolekcji i badge,

przejrzeć sekcję bundle „najczęściej kupowane razem”,

dodać zaznaczone produkty z bundle do koszyka,

otworzyć overlay aranżacji zestawu.

Najważniejsze: przełączanie kolekcji jest realizowane AJAX-em przez dedykowany front controller, więc prawa część, slider, ceny, darmowa dostawa, countdown produktu polecanego, bundle i inne dane aktualizują się dynamicznie bez pełnego reloadu strony.

3. Główne elementy funkcjonalne modułu

3.1. Kolekcje

Każdy rekord kolekcji opisuje jedną sekcję powiązaną z jedną kategorią PrestaShop.

Dla każdej kolekcji można ustawić między innymi:

aktywność,

kategorię,

produkt polecany,

włączenie countdownu z produktu polecanego,

tytuł własny,

obraz desktop,

obraz mobile,

obraz small / xs,

drugie zdjęcie compare,

zdjęcie aranżacji,

pozycję startową compare,

etykietę compare,

limit produktów w sliderze,

tryb slidera (zapętlony lub nie),

sortowanie slidera,

krótki opis,

zakres kolekcji,

4 badge,

bundle produktów.

3.1.1. Sticky bar kolekcji

Moduł posiada dodatkowy sticky bar widoczny podczas scrollowania strony.

Sticky bar:

pojawia się dopiero po przewinięciu ponad główne taby kolekcji,

znika przed końcem sekcji modułu, tak aby nie nachodził na dalszą zawartość strony,

zawiera poziomą listę nazw kolekcji,

zawiera licznik aktualnej kolekcji,

zawiera strzałki poprzednia / następna kolekcja,

umożliwia przełączanie kolekcji tak samo jak główne taby,

jest zsynchronizowany z aktywną kolekcją, licznikiem i stanem AJAX,

na mniejszych szerokościach przewija się poziomo i pokazuje własny custom scrollbar,

po pojawieniu się ukrywa widget Cookiebot, aby nie zasłaniał nazw kolekcji.

3.2. Produkt polecany

Każda kolekcja może mieć przypisany jeden produkt polecany. Jest on wyświetlany w prawej części bloku jako główny bestseller / feature product.

Jeżeli produkt polecany jest ustawiony, moduł:

renderuje jego kartę na froncie,

buduje link do produktu,

pobiera miniaturę do admina,

wyklucza go ze slidera produktów tej samej kolekcji.

3.2.1. Countdown z produktu polecanego

Każda kolekcja może opcjonalnie wyświetlać countdown oparty o aktualnie wybrany produkt polecany.

Działanie jest celowo proste:

countdown nie ma własnego osobnego wyboru produktu,

countdown zawsze działa wyłącznie na podstawie id_featured_product,

włączenie countdownu jest możliwe tylko wtedy, gdy kolekcja ma wybrany produkt polecany.

Na froncie countdown:

wyświetla się pod lewą częścią kolekcji,

jest renderowany przez hook modułu countdown dla ID produktu polecanego,

aktualizuje się dynamicznie po przełączeniu kolekcji AJAX-em,

jest ponownie inicjalizowany po podmianie DOM,

przy pierwszym renderze i po AJAX-ie przechodzi przez stan techniczny loading / ready, aby ograniczyć skakanie i błyski podczas inicjalizacji.

W adminie:

toggle countdownu jest zablokowany, dopóki nie zostanie wybrany produkt polecany,

po wybraniu produktu polecanego toggle odblokowuje się natychmiast, bez potrzeby wcześniejszego zapisu kolekcji,

preview produktu polecanego pokazuje także tekstowy stan countdownu: włączony / wyłączony.

3.3. Slider produktów

Slider pokazuje produkty z kategorii przypisanej do kolekcji.

Obsługiwane tryby sortowania:

position – pozycja w kategorii,

random – losowo,

newest – najnowsze,

price_asc – od najtańszych,

price_desc – od najdroższych,

bestseller – bestsellery.

Dodatkowo można ustawić:

limit produktów od 1 do 20,

tryb infinite / zapętlony.

3.4. Compare image

Jeżeli ustawione jest image_compare_url, lewa część bloku przechodzi w tryb compare:

wyświetlane są dwa obrazy,

użytkownik może przesuwać suwak compare,

można ustawić pozycję startową suwaka,

można ustawić etykietę compare.

3.5. Zdjęcie aranżacji

Jeżeli ustawione jest arrangement_image_url, w sekcji bundle pojawia się przycisk typu:

„Zobacz zestaw w aranżacji”

Po kliknięciu otwierany jest overlay ze zdjęciem aranżacji.

3.6. Najniższa cena w kolekcji

Moduł oblicza najniższą cenę spośród aktywnych produktów w kategorii przypisanej do kolekcji.

Pokazuje:

najniższą cenę,

cenę regularną, jeśli jest promocja,

label zniżki,

flagę promocji.

3.7. Darmowa dostawa od

Moduł pobiera próg darmowej dostawy dla danej kolekcji przez analizę modułu freelivery.

Dane są liczone per aktualna kategoria kolekcji i aktualizują się przy przełączaniu kolekcji AJAX-em.

3.8. Bundle – najczęściej kupowane razem

Każda kolekcja może mieć własną listę produktów bundle.

Dla każdego elementu bundle można ustawić:

produkt,

custom label widoczny na froncie,

aktywność,

pozycję.

Na froncie bundle:

pokazuje produkty w zestawie,

przelicza cenę łączną,

pokazuje cenę regularną,

pokazuje oszczędność,

pokazuje dostawę,

pozwala odznaczać produkty,

pozwala dodać zaznaczone produkty do koszyka.

4. Architektura modułu

Najważniejsze elementy modułu to:

4.1. Główny plik modułu

dfcollection.php

To centralna logika modułu. Zawiera:

instalację,

tworzenie i migracje tabel,

hooki,

render sekcji na homepage,

logikę zapisu kolekcji w BO,

logikę kopiowania kolekcji,

logikę usuwania,

upload i duplikowanie obrazów,

bundle,

cache HTML,

budowę danych dla frontu.

4.2. Front controller AJAX switch

controllers/front/switch.php

Odpowiada za dynamiczne przełączanie kolekcji przez AJAX.

Zwraca JSON zawierający m.in.:

HTML lewej / głównej części,

HTML slidera,

tytuł,

dane o liczbie produktów,

HTML i dane powiązane z countdownem produktu polecanego,

dane o obrazach i ustawieniach kolekcji.

4.3. Front controller bundlecart

controllers/front/bundlecart.php

Odpowiada za dodawanie zaznaczonych produktów z bundle do koszyka.

4.4. Szablony frontowe

Najważniejsze tpl-e znajdują się w:

views/templates/hook/dfcollection.tpl

views/templates/hook/partials/main.tpl

views/templates/hook/partials/slider.tpl

Rola:

dfcollection.tpl – główny kontener sekcji na homepage, taby kolekcji oraz sticky bar,

main.tpl – główny blok aktywnej kolekcji,

slider.tpl – slider produktów.

4.5. JavaScript frontowy

views/js/dfcollection.js

Obsługuje:

przełączanie kolekcji,

preload obrazów,

cache odpowiedzi AJAX,

animacje lewej strony,

animacje featured produktu,

lazy loading obrazów w sliderze,

manual dots,

bundle summary,

add to cart bundle,

overlay aranżacji,

short description expand/collapse,

synchronizację elementów po przełączeniu kolekcji,

dynamiczną podmianę sekcji countdownu produktu polecanego,

ponowną inicjalizację countdownu po AJAX-ie,

obsługę stanów technicznych loading / ready dla countdownu,

odświeżenie miniaturek i eventów po podmianie DOM.

4.6. CSS frontowy

views/css/dfcollection.css

Odpowiada za pełny wygląd sekcji kolekcji na froncie.

4.7. Szablon admina

views/templates/admin/config.tpl

Zawiera:

formularz tworzenia i edycji kolekcji,

dynamiczne preview produktu polecanego,

dynamiczne odblokowanie opcji countdownu po wyborze produktu polecanego,

sekcję ustawień modułu,

sekcję bundle,

upload obrazów,

tabelę wszystkich kolekcji,

akcje: edycja, kopiowanie, usuwanie,

preview produktów i obrazów.

4.8. CSS admina

views/css/admin.css

Styluje cały panel konfiguracyjny modułu w BO.

4.9. JavaScript admina

views/js/dfc-admin-sort.js

Obsługuje przede wszystkim:

sortowanie drag and drop,

AJAX zapisu pozycji,

inicjalizacje pomocnicze w panelu.

Dodatkowa logika inline JS jest też w config.tpl.

4.10. Inline JavaScript w config.tpl

Oprócz osobnego pliku views/js/dfc-admin-sort.js moduł posiada także dodatkową logikę inline JS osadzoną bezpośrednio w config.tpl.

Ta logika odpowiada za:

obsługę pól uploadu plików,

aktualizację wartości suwaka compare_start_percent,

preview produktu polecanego,

dynamiczne odblokowanie toggle countdownu po wyborze produktu polecanego,

aktualizację tekstowego stanu countdownu w preview produktu,

preview produktów bundle,

obsługę pola wykluczonych kategorii,

inicjalizację select2 dla produktu polecanego i bundle.

5. Hooki używane przez moduł

displayHome

Najważniejszy hook frontowy. Moduł renderuje tutaj sekcję kolekcji.

displayHeader

Ładuje:

CSS modułu,

JS modułu,

Media::addJsDef z konfiguracją frontu, np. URL do AJAX switch i bundlecart.

displayBackOfficeHeader

Ładuje:

CSS admina,

JS admina,

jQuery,

Select2,

Sortable.

6. Baza danych

6.1. Tabela główna

ps_dfcollection

Przechowuje dane kolekcji.

Najważniejsze pola:

id_dfcollection

position

active

id_category

id_featured_product

show_featured_countdown

title

image_url

image_url_mobile

image_url_xs

image_compare_url

arrangement_image_url

compare_start_percent

compare_label

slider_limit

slider_infinite

slider_sort

short_description

collection_scope

badge_1

badge_2

badge_3

badge_4

6.2. Tabela bundle

ps_dfcollection_bundle_item

Przechowuje elementy zestawu „najczęściej kupowane razem”.

Najważniejsze pola:

id_dfcollection_bundle_item

id_dfcollection

id_product

custom_label

position

active

7. Instalacja modułu

Co robi install()

Podczas instalacji moduł:

tworzy / aktualizuje tabele bazy,

zapisuje domyślne konfiguracje:

DFC_HEADING

DFC_HEADING_LINK_CATEGORY

DFC_EXCLUDED_CATEGORY_ROOTS

DFC_CACHE_MTIME

rejestruje hooki:

displayHome

displayHeader

displayBackOfficeHeader

tworzy katalog na obrazy modułu.

Katalog obrazów

Obrazy uploadowane przez BO trafiają do:

/modules/dfcollection/img/

Moduł sam pilnuje istnienia tego katalogu przez ensureUploadDir().

8. Upload obrazów

Moduł obsługuje upload:

JPG,

PNG.

Bez rekompresji.

Obsługiwane pola uploadu

image_file – desktop,

image_mobile_file – mobile,

image_xs_file – small,

image_compare_file – compare,

arrangement_image_file – aranżacja.

Co robi upload

handleImageUpload():

waliduje obecność pliku,

sprawdza rozmiar,

sprawdza MIME,

generuje nową nazwę pliku,

zapisuje plik do /modules/dfcollection/img/,

zwraca nowy URL pliku.

9. Kopiowanie kolekcji

Moduł posiada funkcję kopiowania kolekcji z poziomu BO.

Co robi kopiowanie

Po kliknięciu „Kopiuj”:

tworzony jest nowy rekord w ps_dfcollection,

ustawiana jest nowa pozycja na końcu listy,

kolekcja dostaje nowy tytuł z dopiskiem (kopia),

status nowej kopii ustawiany jest jako nieaktywna (active = 0),

wszystkie obrazy lokalne modułu są fizycznie duplikowane jako nowe pliki,

nowe obrazy dostają nowe nazwy i nowe URL-e,

kopiowane są wszystkie dane kolekcji,

kopiowane są także produkty bundle.

Ważne

To nie jest kopiowanie samych URL-i. Dla lokalnych plików modułu wykonywane jest prawdziwe copy() pliku, więc:

klon ma nowe pliki,

usunięcie obrazu z klona nie usuwa obrazu z oryginału,

oryginał i kopia są od siebie niezależne.

Funkcje odpowiedzialne

duplicateLocalImage()

duplicateBundleItems()

blok if (Tools::isSubmit('dfc_duplicate')) w getContent()

10. Usuwanie kolekcji

Po usunięciu kolekcji moduł:

pobiera URL-e zapisanych obrazów,

usuwa lokalne pliki z katalogu modułu,

usuwa bundle items tej kolekcji,

usuwa rekord z ps_dfcollection,

przelicza pozycje od nowa,

czyści cache modułu.

Za usuwanie plików odpowiada:

tryUnlinkIfLocal()

11. Cache

Moduł ma własny prosty cache HTML dla displayHome.

Lokalizacja cache

/var/cache/prod/dfcollection/

Jak działa

Cache jest budowany per:

shop,

language,

currency,

groups,

version,

mtime modułu.

Najważniejsze funkcje

dfcCacheEnabled()

dfcGetCacheDir()

dfcGetContextCacheKey()

dfcCacheRead()

dfcCacheWrite()

dfcCacheClearAllFiles()

dfcCacheBumpMtime()

Kiedy cache jest czyszczony

Po zmianach w module, np.:

zapis kolekcji,

usunięcie kolekcji,

kopiowanie kolekcji,

zmiana kolejności.

12. Logika danych na froncie

Najważniejszą metodą jest:

buildFrontData()

To ona przygotowuje pełen zestaw danych do templatek frontowych, m.in.:

obrazy kolekcji,

featured product,

stan i render countdownu produktu polecanego,

slider produktów,

najniższą cenę,

darmową dostawę,

badge,

opis,

collection scope,

bundle items,

kwoty bundle,

delivery text bundle.

Metoda łączy dane z:

tabel modułu,

kategorii PrestaShop,

produktów PrestaShop,

modułu freelivery,

tabel dfdeliveryinfo_product_source.

13. Dane pobierane z innych modułów / tabel

13.1. freelivery

Jeżeli moduł freelivery jest zainstalowany i aktywny, dfcollection pobiera próg darmowej dostawy dla danej kolekcji.

Funkcja:

getCollectionFreeShippingFromData()

13.2. dfdeliveryinfo_product_source

Z tej tabeli moduł pobiera dane dostawy dla produktów bundle:

tekst dostawy,

koszt dostawy,

darmową dostawę od,

listing delivery text auto.

Funkcja:

getDeliveryInfoForProduct()

14. Najważniejsze metody pomocnicze w module

Produkty i listing

presentProductsForListing()

presentFromCategory()

presentOneById()

Countdown produktu polecanego

logika opiera się na polu show_featured_countdown oraz id_featured_product,

render countdownu na froncie jest powiązany bezpośrednio z aktualnym produktem polecanym,

po zmianie kolekcji countdown jest synchronizowany przez AJAX i ponowną inicjalizację w dfcollection.js.

Ceny i kolekcje

getCollectionProductsCount()

getCollectionLowestPriceData()

getCollectionFreeShippingFromData()

Bundle

getBundleItems()

saveBundleItems()

deleteBundleItems()

getBundleItemsFromRequest()

getBundleFrontItems()

duplicateBundleItems()

getBundleDeliveryText()

getBundleTotalPriceAmount()

getBundleTotalRegularPriceAmount()

getBundleTotalSavingsAmount()

getBundleShippingFromAmount()

formatBundleAmount()

Upload / obrazy

ensureUploadDir()

handleImageUpload()

tryUnlinkIfLocal()

duplicateLocalImage()

probeImageSize()

probeImageDims()

Kategorie i produkty do BO

getCategoryOptions()

getProductOptions()

getExcludedCategoryRootIds()

saveExcludedCategoryRootIds()

getCategoryTreeIds()

Cache

dfcCacheEnabled()

dfcGetCacheDir()

dfcGetContextCacheKey()

dfcCacheRead()

dfcCacheWrite()

dfcCacheClearAllFiles()

dfcCacheBumpMtime()

15. Jak wygląda praca w BO

Tworzenie nowej kolekcji

wejść w konfigurację modułu,

ustawić kategorię,

wybrać produkt polecany,

opcjonalnie włączyć countdown z produktu polecanego,

ustawić tytuł lub zostawić pusty,

ustawić limity i sortowanie slidera,

dodać opis, badge, zakres kolekcji,

wgrać obrazy,

opcjonalnie ustawić compare i aranżację,

opcjonalnie zbudować bundle,

zapisać.

Edycja kolekcji

W tabeli kliknąć Edytuj.

Kopiowanie kolekcji

W tabeli kliknąć Kopiuj.

Usuwanie kolekcji

W tabeli kliknąć Usuń.

Sortowanie kolekcji

W tabeli przeciągać wiersze drag-and-drop.

16. Co jest ważne przy rozwijaniu modułu

16.1. Jeśli zmieniasz dane kolekcji

Po każdej zmianie trzeba pamiętać o cache.

Najlepiej zawsze wywołać:

$this->dfcCacheBumpMtime();

16.2. Jeśli dodajesz nowe pola do kolekcji

Trzeba zaktualizować:

installDb() – nowa kolumna,

ewentualną metodę ensure...Col(),

formularz config.tpl,

zapis w getContent(),

ładowanie danych do edycji,

buildFrontData(),

front tpl / js, jeśli pole jest używane na froncie,

kopiowanie kolekcji, jeśli pole ma być kopiowane.

16.3. Jeśli dodajesz nowe obrazy

Trzeba obsłużyć:

upload,

usuwanie,

kopiowanie,

podgląd w adminie,

ewentualnie preload / AJAX na froncie.

16.4. Jeśli dodajesz nowe funkcje AJAX frontowe

Najlepiej robić to przez osobny front controller modułu, a nie przez mieszanie wszystkiego w jednym switch controllerze.

17. Typowe problemy i na co uważać

Problem: po zmianach nie widać efektu na froncie

Najczęstsza przyczyna:

cache modułu,

cache PrestaShop,

cache przeglądarki,

brak bump mtime.

Problem: obraz usunięty z kopii usuwa się też z oryginału

To nie powinno się dziać, jeśli obraz został skopiowany przez duplicateLocalImage() i ma nowy plik.

Problem: kolekcja po kopiowaniu nie ma nowego obrazu

Sprawdzić:

czy obraz był lokalny i znajdował się w /modules/dfcollection/img/,

czy copy() przeszło poprawnie,

czy plik źródłowy istniał,

czy prawa zapisu do katalogu są poprawne.

Problem: slider nie odświeża się poprawnie po AJAX

Sprawdzić:

dfcollection.js,

czy po podmianie HTML odpalany jest initSlider(),

czy po slick('unslick') i nowym HTML slider jest ponownie inicjalizowany.

Problem: bundle nie przelicza się poprawnie

Sprawdzić:

renderBundleSummary()

calculateBundleSummary()

poprawność data-price-amount i data-regular-price-amount w HTML.

18. Co zawiera admin config.tpl

Panel admina jest podzielony logicznie na:

Sekcja ustawień modułu

nazwa sekcji,

link nagłówka,

wykluczone kategorie dla wyboru produktów.

Sekcja ustawień kolekcji

aktywność,

kategoria,

produkt polecany,

toggle countdownu z produktu polecanego,

preview produktu polecanego ze stanem countdownu,

tytuł,

slider limit,

slider sort,

slider infinite,

compare start,

compare label,

short description,

collection scope,

bundle,

badge.

Sekcja obrazów

desktop,

compare,

aranżacja,

mobile,

small.

Stopka formularza

wyczyść formularz,

zapisz,

kopiuj,

usuń.

Tabela kolekcji

Pokazuje wszystkie rekordy z akcjami.

19. Co zawiera dfcollection.js

To bardzo ważny plik. Odpowiada praktycznie za całe życie sekcji na froncie.

Najważniejsze obszary:

helpery DOM,

preload i cache obrazów,

cache odpowiedzi AJAX,

przełączanie kolekcji,

sticky bar kolekcji,

synchronizacja sticky bara z głównymi tabsami,

sticky arrows i sticky counter,

sticky scrollbar dla tabsów,

pokazywanie i ukrywanie sticky bara zależnie od scrolla,

ukrywanie widgetu Cookiebot przy aktywnym sticky barze,

animacja lewej części,

animacja featured,

obsługa compare,

obsługa slidera Slick,

manualne kropki,

lazy loading obrazów w sliderze,

bundle summary i add to cart,

overlay aranżacji,

tabs drag / scrollbar,

synchronizacja dynamicznie podmienianych sekcji po AJAX-ie,

aktualizacja dfc-lowest-price,

aktualizacja dfc-badges,

aktualizacja dfc-short-description,

aktualizacja dfc-collection-scope,

aktualizacja sekcji countdownu produktu polecanego,

ponowna inicjalizacja countdownu po AJAX-ie,

obsługa klas technicznych is-loading / is-ready dla countdownu,

aktualizacja bundle section.

Jeśli coś na froncie wizualnie zmienia się po przełączaniu kolekcji, prawie na pewno logika jest w dfcollection.js.

20. Szybka mapa: gdzie czego szukać

Chcę zmienić wygląd frontu

Szukaj w:

views/css/dfcollection.css

views/templates/hook/*.tpl

Chcę zmienić zachowanie AJAX przełączania

Szukaj w:

views/js/dfcollection.js

controllers/front/switch.php

Chcę zmienić countdown produktu polecanego

Szukaj w:

views/templates/hook/partials/main.tpl

views/js/dfcollection.js

views/templates/admin/config.tpl

Chcę zmienić dane zwracane do frontu

Szukaj w:

dfcollection.php

buildFrontData()

controllers/front/switch.php

Chcę zmienić zapis / edycję kolekcji w adminie

Szukaj w:

getContent() w dfcollection.php

views/templates/admin/config.tpl

Chcę zmienić upload obrazów

Szukaj w:

handleImageUpload()

duplicateLocalImage()

tryUnlinkIfLocal()

Chcę zmienić bundle

Szukaj w:

getBundleItems()

saveBundleItems()

getBundleFrontItems()

views/templates/hook/partials/main.tpl

views/js/dfcollection.js

21. Podsumowanie

dfcollection to rozbudowany, sprzedażowy moduł homepage, który łączy kilka warstw naraz:

prezentację kolekcji,

produkt polecany,

countdown produktu polecanego,

slider produktów,

compare image,

aranżację,

bundle,

dane cenowe,

próg darmowej dostawy,

AJAX-owe przełączanie,

własny panel administracyjny,

upload i kopiowanie obrazów,

własny cache HTML.

Najważniejsze rzeczy do zapamiętania:

główna logika siedzi w dfcollection.php,

front żyje głównie przez dfcollection.js,

render homepage idzie przez displayHome,

dane kolekcji siedzą w ps_dfcollection,

bundle siedzi w ps_dfcollection_bundle_item,

obrazy modułu siedzą w /modules/dfcollection/img/,

kopiowanie kolekcji robi nowe ID i nowe pliki obrazów,

po każdej zmianie danych trzeba pamiętać o cache.

22. Rekomendacja do dalszego rozwoju

Najbardziej naturalne kolejne kierunki rozwoju tego modułu to:

overlay całej kolekcji ładowany AJAX-em,

szybszy podgląd większej liczby produktów bez przechodzenia do kategorii,

dalsze rozwinięcie bundle,

jeszcze mocniejsze wykorzystanie danych kolekcji jako mini landing page na homepage.

Jeżeli wracasz do tego modułu po czasie, zacznij od:

przeczytania getContent() w dfcollection.php,

sprawdzenia buildFrontData(),

przejrzenia controllers/front/switch.php,

przejrzenia views/js/dfcollection.js,

dopiero potem wchodzenia w tpl i css.

Warto też pilnować, aby wszystkie przyszłe dynamiczne elementy działające podobnie do countdownu były od razu projektowane w dwóch warstwach:

logika renderu początkowego,

logika ponownej inicjalizacji po AJAX-ie.

To jest istotne szczególnie przy modułach zewnętrznych, które po podmianie DOM wymagają dodatkowego reinitu w JavaScript.
