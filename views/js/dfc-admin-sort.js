// modules/dfcollection/views/js/dfc-admin-sort.js
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  function notifySuccess(msg) {
    msg = msg || 'Zaktualizowano pomyślnie.';

    if (typeof window.showSuccessMessage === 'function') {
      window.showSuccessMessage(msg);
      return;
    }

    var box = document.createElement('div');
    box.className = 'alert alert-success';
    box.style.margin = '10px 0';
    box.textContent = msg;

    var host = document.querySelector('.bootstrap .page-head') ||
               document.querySelector('.content-div') ||
               document.body;

    host.insertBefore(box, host.firstChild);

    setTimeout(function () {
      if (box.parentNode) {
        box.parentNode.removeChild(box);
      }
    }, 3000);
  }

  function notifyError(msg) {
    msg = msg || 'Wystąpił błąd.';

    if (typeof window.showErrorMessage === 'function') {
      window.showErrorMessage(msg);
      return;
    }

    var box = document.createElement('div');
    box.className = 'alert alert-danger';
    box.style.margin = '10px 0';
    box.textContent = msg;

    var host = document.querySelector('.bootstrap .page-head') ||
               document.querySelector('.content-div') ||
               document.body;

    host.insertBefore(box, host.firstChild);

    setTimeout(function () {
      if (box.parentNode) {
        box.parentNode.removeChild(box);
      }
    }, 4000);
  }

  ready(function () {
    var tbody = document.getElementById('dfc-sortable');
    if (!tbody) return;

    tbody.querySelectorAll('img, a').forEach(function (el) {
      el.setAttribute('draggable', 'false');
    });

    function order() {
      var ids = [];
      tbody.querySelectorAll('tr[data-id]').forEach(function (tr) {
        ids.push(parseInt(tr.getAttribute('data-id'), 10) || 0);
      });
      return ids.filter(Boolean);
    }

    function save() {
      fetch(tbody.getAttribute('data-url'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'ids=' + encodeURIComponent(order().join(','))
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (resp) {
          if (resp && resp.ok) {
            var i = 1;
            tbody.querySelectorAll('tr').forEach(function (tr) {
              var posCell = tr.querySelector('.dfc-pos');
              if (posCell) {
                posCell.textContent = i++;
              }
            });

            notifySuccess((resp && resp.msg) || 'Kolejność została zapisana.');
          } else {
            notifyError((resp && resp.msg) || 'Nie udało się zapisać kolejności.');
          }
        })
        .catch(function () {
          notifyError('Błąd połączenia przy zapisie kolejności.');
        });
    }

    function freezeRowLayout(tr) {
      var rect = tr.getBoundingClientRect();

      tr.style.width = rect.width + 'px';
      tr.style.height = rect.height + 'px';
      tr.style.boxSizing = 'border-box';

      tr.querySelectorAll('td,th').forEach(function (cell) {
        var cr = cell.getBoundingClientRect();
        cell.style.width = cr.width + 'px';
        cell.style.minWidth = cr.width + 'px';
        cell.style.maxWidth = cr.width + 'px';
        cell.style.height = cr.height + 'px';
        cell.style.boxSizing = 'border-box';
      });
    }

    function unfreezeRowLayout(tr) {
      tr.style.width = '';
      tr.style.height = '';
      tr.style.boxSizing = '';

      tr.querySelectorAll('td,th').forEach(function (cell) {
        cell.style.width = '';
        cell.style.minWidth = '';
        cell.style.maxWidth = '';
        cell.style.height = '';
        cell.style.boxSizing = '';
      });
    }

    function initSortable() {
      if (!window.Sortable) return;

      new Sortable(tbody, {
        handle: '.dfc-handle',
        draggable: 'tr',
        animation: 150,
        forceFallback: true,
        fallbackClass: 'sortable-fallback',
        fallbackOnBody: true,
        fallbackTolerance: 8,
        direction: 'vertical',
        swapThreshold: 0.5,
        chosenClass: 'dfc-chosen',
        ghostClass: 'dfc-ghost',
        cancel: 'a,button,input,textarea,select,.btn,.select2-container,.select2-dropdown,.select2-search__field',

        onStart: function (evt) {
          freezeRowLayout(evt.item);
        },

        onEnd: function (evt) {
          unfreezeRowLayout(evt.item);

          if (evt.oldIndex !== evt.newIndex) {
            save();
          }
        }
      });
    }

    if (window.Sortable) {
      initSortable();
    } else {
      var s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
      s.onload = initSortable;
      document.head.appendChild(s);
    }
  });

  ready(function () {
    var $ = window.jQuery || window.$;
    if (!($ && $.fn && $.fn.select2)) return;

    var WIDTH = '500px';

    try {
      document.querySelectorAll('select.dfc-select2 + .select2').forEach(function (ghost) {
        if (ghost && ghost.parentNode) {
          ghost.parentNode.removeChild(ghost);
        }
      });
    } catch (e) {}

    function closeOthers(current) {
      $('select.dfc-select2').not(current).each(function () {
        var inst = $(this).data('select2');
        if (inst && typeof inst.close === 'function') {
          inst.close();
        }
      });
    }

    function initOne($el, opts) {
      if (
        !$el.length ||
        $el.data('dfcInited') ||
        $el.hasClass('select2-hidden-accessible') ||
        $el.data('select2')
      ) {
        return;
      }

      $el.data('dfcInited', true);
      $el.css('width', WIDTH);
      $el.select2(opts);

      var $container = $el.next('.select2-container');
      if ($container.length) {
        $container.css({
          width: WIDTH,
          maxWidth: WIDTH
        });
      }

      $el.on('select2:open select2:opening', function () {
        closeOthers(this);

        var $c2 = $el.next('.select2-container');
        if ($c2.length) {
          $c2.css({
            width: WIDTH,
            maxWidth: WIDTH
          });
        }

        $('.select2-container--open .select2-dropdown').css({
          width: WIDTH,
          maxWidth: WIDTH
        });
      });
    }

    var $cat = $('select.dfc-select2[name="id_category"]');
    if ($cat.length) {
      $cat.find('> option[value=""]').remove();

      initOne($cat, {
        width: 'style',
        dropdownParent: $(document.body),
        dropdownAutoWidth: false,
        minimumResultsForSearch: 0,
        placeholder: $cat.data('placeholder') || '— szukaj kategorii —',
        allowClear: false
      });
    }

    var $prod = $('select.dfc-select2[name="id_featured_product"]');
    if ($prod.length) {
      if ($prod.find('> option[value=""]').length === 0) {
        $prod.prepend('<option value=""></option>');
      }

      initOne($prod, {
        width: 'style',
        dropdownParent: $(document.body),
        dropdownAutoWidth: false,
        minimumResultsForSearch: 0,
        placeholder: $prod.data('placeholder') || '— szukaj produktu —',
        allowClear: true
      });
    }

    $(document)
      .off('select2:open.dfc select2:opening.dfc')
      .on('select2:open.dfc select2:opening.dfc', 'select.dfc-select2', function () {
        closeOthers(this);
      });
  });

})();