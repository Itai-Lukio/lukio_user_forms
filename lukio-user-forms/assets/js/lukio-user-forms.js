jQuery(document).ready(function ($) {

  let submit_working_class = 'working';

  // remove the no_js indicator
  $('.no_js').removeClass('no_js');

  /**
   * check form required inputs are not empty 
   * 
   * @param {jQuery} form form to check
   * @returns {Bool} true when all the form required inputs are valid
   */
  function check_required_inputs(form) {
    let valid = true;

    form.find('.lukio_user_forms_input_wrapper.luf_required').removeClass('error').each(function () {
      let wrapper = $(this);

      wrapper.find('input, textarea').each(function () {
        let input = $(this),
          type = input.attr('type'),
          valid_input = false;

        // check the input wrapper has a valid input
        if (input.prop('tagName') == 'INPUT' && (type == 'checkbox' || type == 'radio')) {
          valid_input = wrapper.find('input:checked').length != 0;
        } else {
          valid_input = input.val().trim() != '';
        }

        // flag as an invalid form and add error class to the wrapper
        if (!valid_input) {
          wrapper.addClass('error');
          valid = false;
        }
      });

    });

    return valid;
  }

  /**
   * base on wp check_pass_strength
   * @param {jQuery} input form to check
   */
  function check_password_strength(input) {
    let msg = input.closest('.lukio_user_forms_password_inpout_wrapper').siblings('.lukio_user_forms_password_strength'),
      password = input.val();

    msg.removeClass('short bad good strong empty');
    if (!password || '' === password.trim()) {
      msg.addClass('empty').html('');
      return;
    }

    strength = wp.passwordStrength.meter(password, wp.passwordStrength.userInputDisallowedList(), password);

    switch (strength) {
      case -1:
        msg.addClass('bad').html(pwsL10n.unknown);
        break;
      case 2:
        msg.addClass('bad').html(pwsL10n.bad);
        break;
      case 3:
        msg.addClass('good').html(pwsL10n.good);
        break;
      case 4:
        msg.addClass('strong').html(pwsL10n.strong);
        break;
      case 5:
        msg.addClass('short').html(pwsL10n.mismatch);
        break;
      default:
        msg.addClass('short').html(pwsL10n['short']);
    }
    return strength;
  }

  /**
   * check pass1 and pass2 are the same
   * 
   * @param {jQuery} form form object
   * @returns {Bool} true when the checks passed successfully
   * 
   * @author Itai Dotan
   */
  function repeat_password_check(form) {
    let pass = form.find('input[name="pass1"]'),
      repeat_pass = form.find('input[name="pass2"]'),
      repeat_wrapper = repeat_pass.closest('.lukio_user_forms_input_wrapper');

    repeat_wrapper.removeClass('error');
    if (pass.val() !== repeat_pass.val()) {
      repeat_wrapper.addClass('error');
      return false;
    }

    return true;
  }

  /**
   * prepare a form to be sent with ajax
   * 
   * do default test and any given extra check before sending the form
   * 
   * @param {jQuery} form form object
   * @param {null|string} extra_check name of extra checks to run, default `null`
   * 
   * @author Itai Dotan
   */
  function before_sending_form(form, extra_check = null) {
    let submit_btn = form.find('.lukio_user_forms_submit');

    if (submit_btn.hasClass(submit_working_class)) {
      return;
    }

    let valid_required = check_required_inputs(form),
      valid_extra = true;

    if (extra_check !== null && extra_check(form) == false) {
      valid_extra = false
    }

    // no ajax when the required inputs are not valid
    if (!valid_required || !valid_extra) {
      return;
    }

    submit_btn.addClass(submit_working_class);

    let error = form.siblings('.lukio_user_forms_result_error');
    error.removeClass('show').html('');

    send_form(form, submit_btn, error);
  }

  /**
   * send the form using ajax
   * 
   * @param {jQuery} form form object
   * @param {jQuery} submit_btn form submit button object
   * @param {jQuery} error from result error object
   * 
   * @author Itai Dotan
   */
  function send_form(form, submit_btn, error) {
    $.ajax({
      method: 'POST',
      url: lukio_user_forms_data.ajax_url,
      data: form.serialize(),
      success: function (result) {
        if (result) {
          result = JSON.parse(result);
          if (result.status == 'success') {
            switch (result.redirect) {
              case undefined:
                submit_btn.removeClass(submit_working_class);
                break;
              case 'no_redirect':
                form.trigger('form_success', [result]);
                break;
              case 'reload':
                window.location.reload();
                break;
              default:
                window.location.href = result.redirect;
                break;
            }
          } else {
            if (result.error) {
              error.html(result.error).addClass('show');
            }
            submit_btn.removeClass(submit_working_class);
            form.trigger('form_error', [result]);
          }
        }
      }
    });
  }

  /**
   * extra checks to run before sending the register form
   * 
   * @param {jQuery} form form object
   * @returns {Bool} true when the checks passed successfully
   * 
   * @author Itai Dotan
   */
  function register_checks(form) {
    // check the repeat password only when the input exsist
    console.log(form.find('input[name="pass2"]').length)
    if (form.find('input[name="pass2"]').length == 0) {
      $password_check = true;
    } else {
      $password_check = repeat_password_check(form);
    }

    // allow 3rd party to add extra check to run before sending the form. return false when requirement aren't meet
    $pluggable_check = false !== $('body').triggerHandler('lukio_user_forms_register_check', [form]);

    return $password_check && $pluggable_check
  }

  /**
   * show the reset password form
   * 
   * @author Itai Dotan
   */
  async function show_reset_password_form() {
    let url_param = new URLSearchParams(location.search),
      url_var = lukio_user_forms_data.password_reset;
    if (!url_param.has(url_var)) {
      return;
    }

    let wrapper = $('.lukio_user_forms_popup_wrapper.password_reset');
    if (wrapper.length == 0) {
      // if the reset form is not in the page get it before opening. can happen with cached pages
      let data = { action: 'lukio_user_forms_get_reset', login: url_param.get('login') };
      data[url_var] = url_param.get(url_var);
      await $.ajax({
        method: 'GET',
        url: lukio_user_forms_data.ajax_url,
        data: data,
        success: function (result) {
          if (result) {
            result = JSON.parse(result);
            if (result.fragment) {
              let body = $('body');
              body.append(result.fragment);
              // load zxcvbn.min.js in to the page
              body.append(`<script src="${_zxcvbnSettings.src}" type="text/javascript"></script>`);
              wrapper = $('.lukio_user_forms_popup_wrapper.password_reset');
            }
          }
        }
      })
    }
    wrapper.show();
  }
  show_reset_password_form();

  $(document)
    // toggle password input visibility on/off
    .on('click', '.lukio_user_forms_password_toggle', function () {
      let btn = $(this),
        input = btn.siblings('.lukio_user_forms_input.password'),
        show = input.attr('type') == 'password';
      input.attr('type', show ? 'text' : 'password');
      btn.attr('aria-label', show ? lukio_user_forms_data.show_password : lukio_user_forms_data.hide_password);
      btn.find('.lukio_user_forms_password_toggle_icon')
        .removeClass(show ? 'dashicons-visibility' : 'dashicons-hidden')
        .addClass(show ? 'dashicons-hidden' : 'dashicons-visibility');
    })

    // login form submit
    .on('submit', '.lukio_user_forms_form.login', function (e) {
      e.preventDefault();
      before_sending_form($(this));
    })

    // remove error class when new input is entered
    .on('input', '.lukio_user_forms_input', function () {
      $(this).closest('.lukio_user_forms_input_wrapper').removeClass('error');
    })

    // remove error class when checkbox input changed
    .on('change', '.lukio_user_forms_meta_checkbox', function () {
      $(this).closest('.lukio_user_forms_input_wrapper').removeClass('error');
    })

    // check password strength on input
    .on('input', '.lukio_user_forms_input.strength_check', function () {
      let input = $(this),
        submit = input.closest('form').find('.lukio_user_forms_submit'),
        strength = check_password_strength(input);

      if (strength < lukio_user_forms_data.password_strength) {
        submit.attr('disabled', 'disabled');
      } else {
        submit.removeAttr('disabled');
      }
    })

    // reset password submit
    .on('submit', '.lukio_user_forms_form.reset_password', function (e) {
      e.preventDefault();
      before_sending_form($(this), repeat_password_check);
    })

    // prevent popup closing when clicking in side the content
    .on('click', '.lukio_user_forms_popup_content', function (e) {
      e.stopPropagation();
    })

    // close popup when clicking the wrapper
    .on('click', '.lukio_user_forms_popup_wrapper', function () {
      $(this).hide();
    })

    // trigger 'click' on the wrapper to close it
    .on('click', '.lukio_user_forms_popup_close', function () {
      $(this).closest('.lukio_user_forms_popup_wrapper').trigger('click');
    })

    // switch login form between login and lost
    .on('click', '.lukio_user_forms_login_lost_switch', function (e) {
      e.preventDefault();
      $(this).closest('.lukio_user_forms_login_wrapper').find('.lukio_user_forms_login_content, .lukio_user_forms_lost_content').toggleClass('hide_content');
    })

    // lost password submit
    .on('submit', '.lukio_user_forms_form.lost_password', function (e) {
      e.preventDefault();
      before_sending_form($(this));
    })

    // display form success message
    .on('form_success', '.lukio_user_forms_form', function () {
      $(this).addClass('success');
    })

    // register submit
    .on('submit', '.lukio_user_forms_form.register', function (e) {
      e.preventDefault();
      before_sending_form($(this), register_checks);
    })

    // register error respond
    .on('form_error', '.lukio_user_forms_form.register', function (e, respond) {
      if (respond.fields) {
        let form = $(this);
        respond.fields.forEach(name => {
          form.find(`input[name="${name}"]`).closest('.lukio_user_forms_input_wrapper').addClass('error');
        });
      }
    })

    // switch combo forms between login and registration
    .on('click', '.lukio_user_forms_combo_switch', function (e) {
      e.preventDefault();
      $(this).closest('.lukio_user_forms_combo_wrapper').find('.lukio_user_forms_combo_form_wrapper').toggleClass('hide_content');
    });
});

