jQuery(document).ready(function($) {
    var OrekaSearchWidget = new Vue({
        el: '#OrekaSearchSort',
        data: {
          sort : '',
          sorts : [
            {value: '', title: 'مرتبط‌ترین محصول'},
            {value: 'price_desc', title: 'گرانترین محصول'},
            {value: 'price_asc', title: 'ارزانترین محصول'},
            {value: 'totalsales', title: 'محبوب‌ترین محصول'},
            {value: 'totalsales', title: 'پرفروش‌ترین محصول'},
          ],
          baseUrl: null
        },
        created () {
          var url = new URL(window.location.href)
          var urlSortData = url.searchParams.get("sorts")
          if (urlSortData !== null) {
            var t = JSON.parse(urlSortData)
            if (t && t.length > 1) {
              if (t[1].name === 'price' && t[1].descendingOrder === false ) { this.sort = 'price_asc' }
              if (t[1].name === 'price' && t[1].descendingOrder === true ) { this.sort = 'price_desc' }
              if (t[1].name === 'total_sales' && t[1].descendingOrder === true ) { this.sort = 'totalsales' }
            }
          }
          url.searchParams.delete("sorts")
          this.baseUrl = sort_params.url + url.search
        },
        methods: {
          changeSort () {
            var newUrl = ''
            if (this.sort == '') { newUrl = this.baseUrl }
            if (this.sort == 'price_asc') { newUrl = this.baseUrl + '&sorts=' + encodeURIComponent(JSON.stringify([{name: 'stock', descendingOrder: false},{name: 'price', descendingOrder: false}])) }
            if (this.sort == 'price_desc') { newUrl = this.baseUrl + '&sorts=' + encodeURIComponent(JSON.stringify([{name: 'stock', descendingOrder: false},{name: 'price', descendingOrder: true}])) }
            if (this.sort == 'totalsales') { newUrl = this.baseUrl + '&sorts=' + encodeURIComponent(JSON.stringify([{name: 'stock', descendingOrder: false},{name: 'total_sales', descendingOrder: true}])) }
            window.location.href = newUrl
          }
        },
        computed: {
        },
    });
});
