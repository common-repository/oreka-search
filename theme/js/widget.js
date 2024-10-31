jQuery(document).ready(function($) {
    var OrekaSearchWidget = new Vue({
        el: '#OrekaSearchWidget',
        data: {
            timer: null,
            url: null,
            search: null,
            documents: null,
            cacheDocuments: null,
            aggregations: null,
            relaggregations: null,
            urlfacets: [],
            urlrelfacets: [],
            filter: ''
        },
        created () {
          this.timer = Math. floor(Date. now() / 1000)
          this.url = search_params.url + '/?s=' + search_params.params.s + '&oreka=oreka&post_type=' + search_params.params.post_type
          this.search = search_params.search
          this.documents = search_params.documents
          this.cacheDocuments = search_params.cacheDocuments

          var keys = Object.keys(this.cacheDocuments.aggregations)
          if (keys) {
            keys.forEach(element => {
              var xold = this.cacheDocuments.aggregations[element].items
              var xnew = this.documents.aggregations[element].items
              if (xold !== null) {
                for (let oldindex = 0; oldindex < xold.length; oldindex++) {
                  if (this.search !== null) { xold[oldindex].count = '0' }
                  for (let newindex = 0; newindex < xnew.length; newindex++) {
                    if (xold[oldindex].title === xnew[newindex].title ) { xold[oldindex].count = xnew[newindex].count }
                  }
                }
              }
            })
          }
          var relkeys = this.cacheDocuments.aggregations_relevant
          if (relkeys) {
            relkeys.forEach(element => {
              var oldrelitems = element.items
              var newrelitems = this.documents.aggregations_relevant.find(el => el.name === element.name)
              for (let oldindex = 0; oldindex < oldrelitems.length; oldindex++) {
                oldrelitems[oldindex].count = '0'
                if (newrelitems) {
                  for (let newindex = 0; newindex < newrelitems.items.length; newindex++) {
                    if (newrelitems.items[newindex].name === oldrelitems[oldindex].name) { oldrelitems[oldindex].count = newrelitems.items[newindex].count }
                  }
                }
              }
            })
          }

          // get current url params
          var url = new URL(window.location.href)
          var tempUrlFacets = url.searchParams.get("facets")
          if (tempUrlFacets !== null) { this.urlfacets = JSON.parse(tempUrlFacets) }
          var tempUrlRelFacets = url.searchParams.get("relevantfacets")
          if (tempUrlRelFacets !== null) { this.urlrelfacets = JSON.parse(tempUrlRelFacets) }

          // add url facets if available for wc
          if (JSON.parse(search_params.url_params_facets).length > 0) { this.urlfacets = this.urlfacets.concat(JSON.parse(search_params.url_params_facets))  }
          if (JSON.parse(search_params.url_params_relfacets).length > 0) { this.urlfacets = this.urlfacets.concat(JSON.parse(search_params.url_params_relfacets))  }

          // prepare aggregations , add key & check & url
          var keys = Object.keys(this.cacheDocuments.aggregations)
          var temp = []
          if (keys) {
            keys.forEach(element => {
              var tempitem = this.cacheDocuments.aggregations[element].items
              var tempitems = []
              var isopen = true
              if (tempitem !== null) {
                tempitem.forEach(item => {
                  var statusCheck = this.calculateCheckStatus(element, item.title)
                  var statusUrl = this.calculateUrl('facet', element, item.title, statusCheck)
                  tempitems.push({name: item.title, count: item.count, check: statusCheck, url: statusUrl })
                  if (statusCheck) { isopen = true }
                })
                tempitems.sort((a,b) => (a.count < b.count) ? 1 : ((b.count < a.count) ? -1 : 0))
                tempitems.sort((a,b) => (a.check === b.check) ? 0 : ((a.check) ? -1 : 1))
                temp.push({ name: element, items: tempitems, isopen: isopen })
              }
            })
          }
          this.aggregations = temp

          // prepare relaggregations
          var relkeys = this.cacheDocuments.aggregations_relevant
          var reltemp = []
          if (relkeys) {
            relkeys.forEach(element => {
              var tempitem = element.items
              var tempitems = []
              var isopen = false
              if (tempitem !== null) {
                tempitem.forEach(item => {
                  var statusCheck = this.calculateCheckStatus(element.name, item.name)
                  var statusUrl = this.calculateUrl('relevantfacets', element.name, item.name, statusCheck)
                  tempitems.push({name: item.name, count: item.count, check: statusCheck, url: statusUrl })
                  if (statusCheck) { isopen = true }
                })
                tempitems.sort((a,b) => (a.count < b.count) ? 1 : ((b.count < a.count) ? -1 : 0))
                tempitems.sort((a,b) => (a.check === b.check) ? 0 : ((a.check) ? -1 : 1))
                reltemp.push({ name: element.name, items: tempitems, isopen: isopen })
              }
            })
          }
          this.relaggregations = reltemp
          // translate and hide based on admin panel settings
          this.aggregations.forEach(function(item, index, object) {
            const found = search_params.filters_list.filter(e => e.name === item.name)
            if (found.length > 0) {
              item.title = found[0].title
              item.show = found[0].show
            } else {
              item.title = item.name
              item.show = true
            }
          })
          this.relaggregations.forEach(function(item, index, object) {
            const found = search_params.filters_list.filter(e => e.name === item.name)
            if (found.length > 0) {
              item.title = found[0].title
              item.show = found[0].show
            } else {
              item.title = item.name
              item.show = true
            }
          })
        },
        methods: {
          toggle: function (item) {
            var check_aggregation_for_toggle = this.aggregations.find(o => o.name === item.name)
            var check_relaggregation_for_toggle = this.relaggregations.find(o => o.name === item.name)
            if (check_aggregation_for_toggle) { check_aggregation_for_toggle.isopen = !item.isopen }
            if (check_relaggregation_for_toggle) { check_relaggregation_for_toggle.isopen = !item.isopen }
          },
          calculateCheckStatus: function (parent, item) {
            var result = false
            this.urlfacets.forEach(element => {
              element.fixval = decodeURIComponent(element.fixval)
              if (element.fixval.includes('|')) { if (element.name === parent && element.fixval.split('|').includes(item)) { result = true } }
              else { if (element.name === parent && element.fixval === item) { result = true } }
            })
            this.urlrelfacets.forEach(element => {
              element.fixval = decodeURIComponent(element.fixval)
              if (element.fixval.includes('|')) { if (element.name === parent && element.fixval.split('|').includes(item)) { result = true } }
              else { if (element.name === parent && element.fixval === item) { result = true } }
            })
            return result
          },
          calculateUrl: function (type, parent, item, check) {
            item = encodeURIComponent(item)
            var tempUrlFacet = JSON.parse(JSON.stringify(this.urlfacets))
            var tempUrlRelFacet = JSON.parse(JSON.stringify(this.urlrelfacets))
            if (type === 'facet') {
              if (check) { // remove item from url
                var t = tempUrlFacet.find(el => el.name === parent)
                if (t.fixval.includes('|')) {
                  // remove item from parent
                  var tt = t.fixval.split('|')
                  tt.splice(tt.findIndex(el => el === item), 1)
                  t.fixval = tt.join('|')
                } else { // remove item completly
                  tempUrlFacet.splice(tempUrlFacet.findIndex(el => el.name === parent && el.fixval === item), 1)
                }
              } else { // add item to url
                if (tempUrlFacet.some(el => el.name === parent)) { // check if parent exits in url
                  // add item to current parent
                  var t = tempUrlFacet.find(el => el.name === parent)
                  t.fixval = t.fixval + '|' + item
                } else {
                  // add item & parent
                  tempUrlFacet.push({ name: parent, fixval: item })
                }
              }
            } else {
              if (check) { // remove item from url
                var t = tempUrlRelFacet.find(el => el.name === parent)
                if (t.fixval.includes('|')) {
                  // remove item from parent
                  var tt = t.fixval.split('|')
                  tt.splice(tt.findIndex(el => el === item), 1)
                  t.fixval = tt.join('|')
                } else { // remove item completly
                  tempUrlRelFacet.splice(tempUrlRelFacet.findIndex(el => el.name === parent && el.fixval === item), 1)
                }
              } else { // add item to url
                if (tempUrlRelFacet.some(el => el.name === parent)) { // check if parent exits in url
                  // add item to current parent
                  var t = tempUrlRelFacet.find(el => el.name === parent)
                  t.fixval = t.fixval + '|' + item
                } else {
                  // add item & parent
                  tempUrlRelFacet.push({ name: parent, fixval: item })
                }
              }
            }
            return this.url + '&facets=' + encodeURIComponent(JSON.stringify(tempUrlFacet)) + '&relevantfacets=' + encodeURIComponent(JSON.stringify(tempUrlRelFacet))
          }
        },
        computed: {
          fiiltered_aggregations () {
            const internalThis = this
            var internal_aggregations = JSON.parse(JSON.stringify(internalThis.aggregations))
            var part_1 = internal_aggregations.filter(function (aggregation) { return aggregation.name.includes(internalThis.filter) })
            var part_2 = []
            internal_aggregations.forEach(element => {
              var part_2_temp = element.items.filter(function (item) { return item.name.includes(internalThis.filter) })
              if (part_2_temp.length > 0) {
                element.items = part_2_temp
                part_2.push(element)
              }
            })
            // merge
            var part_3 = part_1.concat(part_2)
            if (internalThis.filter != '') { part_3.forEach(element => { element.isopen = true }) }
            // remove dublicates
            var part_4 = part_3.filter((value, index) => part_3.indexOf(value) === index && value.show === true)
            // remove 0 items
            part_4.forEach(element => {
              element.items = element.items.filter(function (items) { return items.count > 0 })
            })
            return part_4
          },
          fiiltered_relaggregations () {
            const internalThis = this
            var internal_relaggregations = JSON.parse(JSON.stringify(internalThis.relaggregations))
            var part_1 = internal_relaggregations.filter(function (aggregation) { return aggregation.name.includes(internalThis.filter) })
            var part_2 = []
            internal_relaggregations.forEach(element => {
              var part_2_temp = element.items.filter(function (item) { return item.name.includes(internalThis.filter) })
              if (part_2_temp.length > 0) {
                element.items = part_2_temp
                part_2.push(element)
              }
            })
            // merge
            var part_3 = part_1.concat(part_2)
            if (internalThis.filter != '') { part_3.forEach(element => { element.isopen = true }) }
            // remove duplicates
            var part_4 = part_3.filter((value, index) => part_3.indexOf(value) === index)
            // remove 0 items
            part_4.forEach(element => {
              element.items = element.items.filter(function (items) { return items.count > 0 })
            })
            return part_4.filter(function (relagg) { return relagg.items.length > 0 && relagg.show === true })
          }
        },
    });
});
