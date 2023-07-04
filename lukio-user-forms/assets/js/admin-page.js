jQuery(document).ready(function ($) {
  /**
 * add or update the url query with the new param and value
 * 
 * @param {string} param url param to update
 * @param {string} newval param new value
 * @param {string} search url query
 * @returns {string} updated url query
 * 
 * @author Itai Dotan
 */
  function replace_query_param(param, newval, search) {
    let regex = new RegExp("([?;&])(" + param + "[^&;]*[;&]?)"),
      query = search.replace(regex, "$1").replace(/[?&]$/, '');

    return query + (newval ? (query.length > 0 ? "&" : "?") + param + "=" + newval : '');
  }

  // switch option tabs
  $('.lukio_user_forms_options_tab').on('click', function () {
    let tab = $(this);
    if (tab.hasClass('active')) {
      return;
    }
    let new_tab_index = tab.data('tab');
    $('.lukio_user_forms_options_tab.active, .lukio_user_forms_options_tab_content.active').removeClass('active');
    $(`.lukio_user_forms_options_tab[data-tab="${new_tab_index}"], .lukio_user_forms_options_tab_content[data-tab="${new_tab_index}"]`).addClass('active');

    window.history.replaceState({}, "", window.location.pathname + replace_query_param('tab', new_tab_index, window.location.search));
  });

  /**
   * initialize the editor on the extra checkbox textarea which havent yet been initialized
   * 
   * @author Itai Dotan
   */
  function textarea_to_visual() {
    let textareas = $('.lukio_user_forms_extra_checkbox_text:not(.template):not(.tiny_init)');
    textareas.each(function () {
      let el = $(this);
      el.addClass('tiny_init')
      wp.editor.initialize(el.attr('id'), {
        tinymce: {
          toolbar1: 'bold,italic,link'
        }
      });
    });
  }
  textarea_to_visual();

  // copy the shortcode to clipboard  
  $('.lukio_user_forms_options_shortcode_button').on('click', function () {
    let btn = $(this);
    navigator.clipboard.writeText(btn.siblings('.lukio_user_forms_options_shortcode').text());
    btn.addClass('copied');
    setTimeout(() => {
      btn.removeClass('copied');
    }, 2000);
  });

  // trigger the copy button
  $('.lukio_user_forms_options_shortcode').on('click', function () {
    $(this).siblings('.lukio_user_forms_options_shortcode_button').trigger('click');
  });

  // toggle between the switch 2 display options if there are any
  $('.lukio_user_forms_switch_input').on('change', function () {
    $(`[data-toggle="${$(this).attr('name')}"]`).toggleClass('hide_option');
  });

  $(document)
    // remove extra checkbox row
    .on('click', '.lukio_user_forms_extra_checkboxes_remove:not(.template)', function () {
      $(this).closest('.lukio_user_forms_extra_checkbox').remove();
    })

    // add new extra checkbox row
    .on('click', '.lukio_user_forms_extra_checkboxes_add', function () {
      let btn = $(this),
        wrapper = btn.closest('.lukio_user_forms_extra_checkboxes_wrapper'),
        template_html = wrapper.find('.lukio_user_forms_extra_checkbox.template').prop('outerHTML');
      btn.before(template_html.replaceAll('%d', Date.now()).replaceAll('template', ''));
      textarea_to_visual();
    })

    .on('click', '.extra_checkboxes_help', function () {
      let wrapper = $('.extra_checkboxes_help_tooltip_wrappper'),
        new_height = 0;

      // get the wrapper height before opening the wrapper
      if (!wrapper.hasClass('show')) {
        wrapper.css('height', 'auto');
        new_height = wrapper.css('height');
        wrapper.css('height', '');
      }

      wrapper.animate({ height: new_height }, 400, function () {
        wrapper.css('height', new_height ? 'auto' : '');
      });
      wrapper.toggleClass('show');
    });
});
