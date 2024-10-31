jQuery(document).ready(function($) {
  var OrekaSearchForm = new Vue({
      el: '#OrekaSearchForm',
      data: {
        search: ajax_object.q,
        timer: null,
        results: null,
        loading: false,
        mouse: false
      },
      created () {
      },
      methods: {
        searchTimeOut() {
          if (ajax_object.ajax === 'false') { return false }
          if (this.timer) {
              clearTimeout(this.timer)
              this.timer = null
          }
          this.timer = setTimeout(() => { this.autocomplete() }, 800)
        },
        autocomplete: function () {
          this.loading = true
          this.results = null
          var internalThis = this
          var data = {
            'action': 'oreka_search_autocomplete_ajax',
            'search': this.search,
            'security': ajax_object.ajax_nonce
          }
          jQuery.post(ajax_object.ajaxurl, data, function(response) {
            internalThis.loading = false
            internalThis.mouse = true
            internalThis.results = response.data
          })
          return false
        },
        clicked: function(i) {
          this.search = i
          this.$nextTick(() => { this.$refs.OrekaSearchForm.submit() })
        },
        mouseovered: function() {
          this.mouse = true
        },
        mouseleaved: function() {
          this.mouse = false
        }
      },
      computed: {
        showoverlay () {
          if (this.loading) { return true }
          if (this.mouse & this.results != null) { return true }
          return false
        },
        noresult () {
          if (this.results === null) { return false }
          if (Object.keys(this.results).length === 0) { return true }
          return false
        },
        didyoumean () {
          if (!this.results) { return null }
          return this.results.didyoumean
        },
        querysuggestions () {
          if (!this.results) { return null }
          return this.results.querysuggestions
        },
        titlesuggestions () {
          if (!this.results) { return null }
          if (!this.results.titlesuggestions) { return null }
          return this.results.titlesuggestions.items
        }
      },
  });
});
