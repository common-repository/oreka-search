jQuery(document).ready(function($) {
  var OrekaSearchAdmin = new Vue({
      el: '#OrekaSearchAdmin',
      data: {
        tab: 1,
        oreka: {
          loading: false,
          msg: '',
          msg_type: '',
          state: '',
          customer_id : '',
          project_id : '',
          catalog_id : '',
          token : '',
          activation : '',
          form_replacement : '',
          ajax : '',
          ingestion : '',
          woocommerce : '',
          metatable : '',
          filters: []
        },
        analytics: {
          documentsCount: '?',
          facetCount: '?',
          noResultCount: '?',
          queryCount: '?',
          lastSearches: [],
          lastFacets: [],
          statusByNoResultQuery: [],
          statusByQuery: []
        },
        analyticsDateTo: new Date().toISOString().slice(0, 10),
        analyticsDateFrom: new Date( new Date().getFullYear(),  new Date().getMonth(),  new Date().getDate()-7).toISOString().slice(0, 10),
        register_data: { email : '', namespaceName : window.location.hostname.replace('www.','').replace('.','').substring(0,20), password : '', captcha_key : '', captcha_value : '', registrationChannel: 'wordpress', directRegisterProduct: 'OREKA', directRegisterActivationUrl: 'https://oreka.cloud/registration/activation', directRegisterParams: { planId: '88a9a505-806c-4115-9e32-c75e4f7aa292', productName: 'اورکا', productDescription: 'سرویس جستجوی هوشمند', forwardingToken: '', customerNameFa: '' } },
        register_repassword : '',
        register_captcha : null,
        register_msg : '',
        register_err : '',
        is_tally : false
      },
      created () {
        var internalThis = this
        internalThis.oreka.loading = true
        var data = { 'action': 'orekasearch_admin_get' }
        jQuery.post(ajaxurl, data, function(response) {
          internalThis.oreka = response.data
        }).fail(function(response) {
          console.log(response)
          alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
          internalThis.oreka.loading = false
        })
      },
      methods: {
        getCaptcha: function () {
          internalThis = this
          internalThis.oreka.loading = true
          jQuery.post('https://oreka.cloud/panel/organizationapi/api/v1/public/captcha', [], function(response) {
            internalThis.register_captcha = response.captcha_image
            internalThis.register_data.captcha_key = response.captcha_key
            internalThis.register_data.captcha_value = ''
            internalThis.oreka.loading = false
          }).fail(function(response) {
            internalThis.oreka.loading = false
            console.log(response)
          })
        },
        register: function () {
          this.register_msg = ''
          this.register_err = ''
          // validation
          if (this.is_tally && this.register_data.directRegisterParams.customerNameFa === '') { this.register_err = 'نام فروشگاه در تالی وارد نشده است.' }
          if (this.is_tally && this.register_data.directRegisterParams.forwardingToken === '') { this.register_err = 'توکن تالی وارد نشده است.' }
          if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.register_data.email)) {} else { this.register_err = 'خطا : ایمیل به صورت صحیح وارد نشده است.' }
          if (/^[a-zA-Z][a-zA-Z][a-zA-Z]+$/.test(this.register_data.namespaceName)) {} else { this.register_err = 'خطا : شناسه کاربری فقط باید از حروف انگلیسی تشکیل شده باشد و بیش از ۳ حرف باشد.' }
          if (/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#\^_~\-\(\)\.\,])[A-Za-z\d@$!%*?&#\^_~\-\(\)\.\,]{10,}$/.test(this.register_data.password)) {} else { this.register_err = 'خطا : رمز عبور باید حداقل ۱۰ کاراکتر و شامل حداقل یک رقم، یک حرف بزرگ، یک حرف کوچک و یکی از کاراکتر های خاص باشد.' }
          if (this.register_data.password != this.register_repassword) { this.register_err = 'خطا : رمز عبور و تکرار رمز عبور مشابه نیستند.' }
          if (this.register_data.captcha_value.length != 4) { this.register_err = 'خطا : کد امنیتی شامل ۴ کاراکتر میباشد.' }
          if (this.register_err != '') { return false }
          // prepare data
          const prepared_data = {
            captcha_key: this.register_data.captcha_key,
            captcha_value: this.register_data.captcha_value,
            email: this.register_data.email,
            password: this.register_data.password,
            namespaceName: this.register_data.namespaceName,
            registrationChannel: this.register_data.registrationChannel,
            directRegisterProduct: this.register_data.directRegisterProduct,
            directRegisterActivationUrl: this.register_data.directRegisterActivationUrl,
            directRegisterParams: {
              planId:  this.register_data.directRegisterParams.planId,
              productName:  this.register_data.directRegisterParams.productName,
              productDescription:  this.register_data.directRegisterParams.productDescription,
            }
          }
          // tally check
          if (this.is_tally) {
            prepared_data.registrationChannel = 'plugin'
            prepared_data.directRegisterParams.customerNameFa = this.register_data.directRegisterParams.customerNameFa
            prepared_data.directRegisterParams.forwardingToken = this.register_data.directRegisterParams.forwardingToken
          }
          // registeration
          this.register_msg = 'ارسال اطلاعات به سرویس اورکا ... '
          internalThis = this
          internalStatus = null
          fetch('https://oreka.cloud/panel/organizationapi/api/v1/public/register/direct', {
            method: 'POST',
            headers: { 'Accept': 'application/json, text/plain, */*', 'Content-Type': 'application/json' },
            body: JSON.stringify(prepared_data)
          })
          .then((response) => {
            internalStatus = response.status
            return response.json()
          })
          .then(function (res) {
              // validation
              error_msg = ''
              if (res.Reason === 'BAD_REQUEST') { error_msg = error_msg + 'متاسفانه درخواست شما با خطا مواجه شد. لطفا فیلدهای زیر را بررسی کرده و مجددا تلاش کنید.' + '\n' }
              if (res.Reason === 'EMAIL_ALREADY_EXIST') { error_msg = error_msg + 'شناسه کاربری (ایمیل) قبلا ثبت‌نام شده است.' + '\n' }
              if (res.Reason === 'NAMESPACENAME_ALREADY_EXIST') { error_msg = error_msg + 'این نام مشتری قبلا در اورکا ثبت شده است.' + '\n' }
              if (res.Reason === 'INVALID_PASSWORD') { error_msg = error_msg + 'رمز عبور وارد شده معتبر نیست.' + '\n' }
              if (res.Reason === 'INVALID_EMAIL') { error_msg = error_msg + 'شناسه کاربری (ایمیل) وارد شده معتبر نیست.' + '\n' }
              if (res.Reason === 'INVALID_CAPTCHA') { error_msg = error_msg + 'کد امنیتی وارد شده معتبر نیست.' + '\n' }
              if (res.Reason === 'INVALID_NAMESPACENAME') { error_msg = error_msg + 'نام مشتری وارد شده معتبر نیست.' + '\n' }
              if (res.Reason === 'INVALID_FORWARDINGTOKEN') { error_msg = error_msg + 'توکن معرفی‌نامه تالی وارد شده معتبر نیست.' + '\n' }
              if (res.Reason !== '' && error_msg === '') { error_msg = 'خطا :‌ ' + res.Reason }
              internalThis.register_msg = ''
              internalThis.register_err = error_msg
            })
            .catch (function (error) {
              if (internalStatus === 201) { // if no response and status code 201 then it is okey
                internalThis.register_msg = 'با تشکر. درخواست ثبت‌نام شما ثبت شد. یک ایمیل شامل تنظیمات اتصال طی چند دقیقه آینده برای شما ارسال خواهد شد.'
                internalThis.register_err = ''
              } else { // invalid response
                internalThis.register_msg = ''
                internalThis.register_err = 'درخواست ثبت‌نام ارسال نشد. لطفا مجددا تلاش کنید.'
              }
            }).then(function (res) {
              // regenerate captcha if error occured
              if (internalThis.register_err != '') {
                internalThis.register_captcha = ''
                internalThis.getCaptcha()
              }
            })
        },
        save_1: function () {
          var internalThis = this
          internalThis.oreka.loading = true
          var data = { 'action': 'orekasearch_admin_set_1', 'oreka': { customer_id : internalThis.oreka.customer_id, project_id : internalThis.oreka.project_id, catalog_id : internalThis.oreka.catalog_id, token : internalThis.oreka.token } }
          jQuery.post(ajaxurl, data, function(response) {
            internalThis.oreka = response.data
          }).fail(function(response) {
            console.log(response)
            alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
            internalThis.oreka.loading = false
          })
        },
        save_2: function () {
          var internalThis = this
          internalThis.oreka.loading = true
          var data = { 'action': 'orekasearch_admin_set_2', 'oreka': { activation: internalThis.oreka.activation,  form_replacement: internalThis.oreka.form_replacement,  ajax: internalThis.oreka.ajax,  ingestion: internalThis.oreka.ingestion,  woocommerce: internalThis.oreka.woocommerce,  metatable: internalThis.oreka.metatable } }
          jQuery.post(ajaxurl, data, function(response) {
            internalThis.oreka = response.data
          }).fail(function(response) {
            console.log(response)
            alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
            internalThis.oreka.loading = false
          })
        },
        save_3: function () {
          var internalThis = this
          internalThis.oreka.loading = true
          var data = { 'action': 'orekasearch_admin_set_filters', 'oreka': { filters: internalThis.oreka.filters } }
          jQuery.post(ajaxurl, data, function(response) {
            internalThis.oreka = response.data
          }).fail(function(response) {
            console.log(response)
            alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
            internalThis.oreka.loading = false
          })
        },
        sleep: function (ms) {
          return new Promise((resolve) => { setTimeout(resolve, ms) })
        },
        ingestParts: async function (part, parts) {
          await this.sleep(20000 * part)
          var internalThis = this
          internalThis.oreka.loading = true
          var data = { 'action': 'orekasearch_admin_ingest' , 'part' : part }
          jQuery.post(ajaxurl, data, function(response) {
            internalThis.oreka = response.data
            if(parts - 1 === part) { internalThis.oreka.loading = false } else { internalThis.oreka.loading = true }
          }).fail(function(response) {
            console.log(response)
            alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
            internalThis.oreka.loading = false
          })
        },
        ingest: function () {
          var internalThis = this
          internalThis.oreka.loading = true
          var data = { 'action': 'orekasearch_admin_preingest' }
          jQuery.post(ajaxurl, data, function(response) {
            internalThis.oreka = response.data
            var temp_parts = response.data.parts
            for (let ingest_counter = 0; ingest_counter < temp_parts; ingest_counter++) {
              internalThis.ingestParts(ingest_counter, temp_parts)
            }
          }).fail(function(response) {
            console.log(response)
            alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
            internalThis.oreka.loading = false
          })
        },
        clear: function () {
          var internalThis = this
          internalThis.oreka.loading = true
          var data = { 'action': 'orekasearch_admin_clear' }
          jQuery.post(ajaxurl, data, function(response) {
            internalThis.oreka = response.data
          }).fail(function(response) {
            console.log(response)
            alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
            internalThis.oreka.loading = false
          })
        },
        getAnalytics: function () {
          var internalThis = this
          internalThis.oreka.loading = true
          var data = { 'action': 'orekasearch_admin_analytics', 'from' : this.analyticsDateFrom, 'to' : this.analyticsDateTo }
          jQuery.post(ajaxurl, data, function(response) {
            internalThis.analytics = response.data
            internalThis.oreka.loading = false
          }).fail(function(response) {
            console.log(response)
            alert('متاسفانه در اجرای درخواست شما خطایی رخ داد. لطفا مجدد تلاش کنید.')
            internalThis.oreka.loading = false
          })
        },
        changeTab: function (id) {
          this.oreka.msg = false
          this.tab = id
          if (id === 8) { this.getAnalytics(); }
          if (id === 2) { this.getCaptcha(); }
        },
        menuIcon: function (tab) {
          if (tab == 2) {
            if (this.oreka.state == '_not_registered_') { return 'i1' }
            if (this.oreka.state == '_not_confirmd_') { return 'i1' }
            if (this.oreka.state == '_not_activated_') { return 'i3' }
            if (this.oreka.state == '_active_') { return 'i3' }
            return 'i6'
          }
          if (tab == 3) {
            if (this.oreka.state == '_not_registered_') { return 'i2' }
            if (this.oreka.state == '_not_confirmd_') { return 'i2' }
            if (this.oreka.state == '_not_activated_') { return 'i3' }
            if (this.oreka.state == '_active_') { return 'i3' }
            return 'i6'
          }
          if (tab == 4) {
            if (this.oreka.state == '_not_registered_') { return 'i4' }
            if (this.oreka.state == '_not_confirmd_') { return 'i4' }
            if (this.oreka.state == '_not_activated_') { return 'i3' }
            if (this.oreka.state == '_active_') { return 'i3' }
            return 'i6'
          }
          if (tab == 5) {
            if (this.oreka.state == '_not_registered_') { return 'i4' }
            if (this.oreka.state == '_not_confirmd_') { return 'i4' }
            if (this.oreka.state == '_not_activated_') { return 'i2' }
            if (this.oreka.state == '_active_') { return 'i3' }
            return 'i6'
          }
          if (tab == 6) {
            if (this.oreka.state == '_not_registered_') { return 'i4' }
            if (this.oreka.state == '_not_confirmd_') { return 'i4' }
            if (this.oreka.state == '_not_activated_') { return 'i4' }
            if (this.oreka.state == '_active_' && this.oreka.woocommerce) { return 'i3' }
            if (this.oreka.state == '_active_' && !this.oreka.woocommerce) { return 'i2' }
            return 'i6'
          }
          if (tab == 7) {
            if (this.oreka.state == '_not_registered_') { return 'i4' }
            if (this.oreka.state == '_not_confirmd_') { return 'i4' }
            if (this.oreka.state == '_not_activated_') { return 'i4' }
            if (this.oreka.state == '_active_' && this.oreka.form_replacement) { return 'i2' }
            if (this.oreka.state == '_active_' && !this.oreka.form_replacement) { return 'i3' }
            return 'i6'
          }
          if (tab == 8) {
            if (this.oreka.state == '_not_registered_') { return 'i4' }
            if (this.oreka.state == '_not_confirmd_') { return 'i4' }
            if (this.oreka.state == '_not_activated_') { return 'i3' }
            if (this.oreka.state == '_active_') { return 'i3' }
            return 'i6'
          }
        }
      }
  })
})
