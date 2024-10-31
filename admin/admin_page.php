<div class="wrap">
  <div id="OrekaSearchAdmin" v-cloak>
    <div class="cover" v-bind:class="{ loading: oreka.loading }"><div>در حال انجام عملیات<br>لطفا منتظر بمانید</div></div>
    <div class="header">
      <div class="logo"><img src="<?php echo plugins_url( '../theme/logo.png', __FILE__ ); ?>"></div>
      <h2>اورکا : سرویس هوشمند جستجو</h2>
    </div>
    <div class="container">
      <div class="menu">
        <ul>
          <li v-bind:class="{ active: tab == 1 }"><a v-on:click="changeTab(1)">معرفی</a></li>
          <li v-bind:class="{ active: tab == 2 }"><a v-on:click="changeTab(2)">۱ - ثبت‌نام <span v-bind:class="menuIcon(2)"></span></a></li>
          <li v-bind:class="{ active: tab == 3 }"><a v-on:click="changeTab(3)">۲ - تنظیمات اتصال <span v-bind:class="menuIcon(3)"></span></a></li>
          <li v-bind:class="{ active: tab == 4 }"><a v-on:click="changeTab(4)">۳ - همگام‌سازی اطلاعات <span v-bind:class="menuIcon(4)"></span></a></li>
          <li v-bind:class="{ active: tab == 5 }"><a v-on:click="changeTab(5)">۴ - فعال‌سازی و تنظیمات <span v-bind:class="menuIcon(5)"></span></a></li>
          <li v-bind:class="{ active: tab == 6 }"><a v-on:click="changeTab(6)">۵ - فیلترها در ووکامرس <span v-bind:class="menuIcon(6)"></span></a></li>
          <li v-bind:class="{ active: tab == 7 }"><a v-on:click="changeTab(7)">۶ - شورت کد (ShortCode) <span v-bind:class="menuIcon(7)"></span></a></li>
          <li v-bind:class="{ active: tab == 8 }"><a v-on:click="changeTab(8)">۷ - گزارش جستجو در اورکا <span v-bind:class="menuIcon(8)"></span></a></li>
          <li v-bind:class="{ active: tab == 9 }"><a v-on:click="changeTab(9)">۸ - راهنمای استفاده و تغییرات <span class="i5"></span></a></li>
        </ul>
      </div>
      <div class="content" v-bind:class="{ first_tab_selected: tab == 1 }">
        <div class="msg"  v-bind:class="{show: oreka.msg, info: oreka.msg_type == 'info', alert: oreka.msg_type == 'alert', success: oreka.msg_type == 'success' }">{{oreka.msg}}<span v-on:click="oreka.msg = false">x</span></div>
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 1">
          <h3>معرفی سرویس جستجوی هوشمند اورکا</h3>
          <p>محصول جستجوی هوشمند اورکا توسط شرکت هوش مصنوعی دلفین در راستای بهبود تجربه جستجوی کاربران بازار الکترونیک کشور و با تاکید بر نیازمندی‌های سایت‌های فارسی زبان توسعه داده است. پنلی که هم اکنون با نصب پلاگین این محصول مشاهده می‌کنید، جهت استفاده ساده‌تر و سریع سایت‌های عمومی و با تنظیمات پیش‌فرض در اختیار شما قرار گرفته است. تلاش ما بر این بوده است که با کمترین تنظیمات و کوشش، بتوانید بیشترین نیازهای خود را پوشش دهید.</p>
          <p>شما با نصب پلاگین اورکا قادر خواهید بود که تمام اطلاعات سایت خود را به صورت خودکار ایندکس کرده و پس از آن از امکاناتی نظیر اصلاح متن جستجو شده توسط کاربران، پیشنهاد متن بهتر برای جستجو به کاربر، نمایش متون جستجوی محبوب، ویجت فیلترینگ هوشمند متغیر بر اساس نتایج جستجو بهره‌مند شوید. این امکانات در کنار قابلیت اصلی این موتور جستجو، یعنی بهبود کم‌نظیر نتایج جستجو در زبان فارسی خواهد بود که به  تنهایی حس متفاوتی در استفاده از سایت شما ایجاد خواهد کرد.</p>
          <p>این محصول بخشی از محصول اصلی شرکت دلفین به نام اورکا است و از موتور هوشمند اورکا به عنوان سرورهای اصلی پردازشی استفاده می‌کند. شما می‌توانید با ورود به پنل اورکا بر روی سرورهای اصلی، علاوه بر تنظیمات محدود پلاگین، از امکانات بیشتری نظیر تعریف دیکشنری‌های اختصاصی سایت خود، وزن‌دهی به فیلدهای مورد جستجو، تغییر روش‌های اولویت‌دهی به نتایج و مرتب‌سازی و فرایند‌های کنترل همگام‌سازی اطلاعات برخوردار شوید. به صورت طبیعی سایت‌هایی با بازدید بالا و یا محتوای خاص برای ایجاد تجربه بهتر نیاز به دسترسی به این پنل و شخصی‌سازی تنظیمات خواهند داشت.</p>
          <p>شرکت هوش مصنوعی دلفین علاوه بر محصولات مبتنی بر جستجو، در حال حاضر در سبد خود چت‌بات هوشمند و موتور پیشنهاد محصول را دارد. جهت اطلاع از نحوه استفاده از این محصولات و دریافت اطلاعات بیشتر می‌توانید به <a target="_blank" href="https://dolphinai.ir/">وب‌سایت شرکت</a> مراجعه کنید.</p>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 2">
          <h3>ثبت‌نام در سرویس جستجوی هوشمند اورکا</h3>
          <div class="register_table">
            <div v-if="register_msg != ''"><div class="register_msg">{{register_msg}}</div></div>
            <div v-if="register_msg == ''" class="table_2">
              <div class="big_row register_err" v-show="register_err != ''" >{{register_err}}</div>
              <div class="big_row"><h4>۱ - شناسه کاربری ( ایمیل )</h4><input name="register_email" type="text" class="inputbox" v-model="register_data.email" /></div>
              <div class="big_row"><h4>۲ - نام مشتری ( انگلیسی )</h4><input name="register_customer" type="text" class="inputbox" v-model="register_data.namespaceName" /></div>
              <div><h4>۳ - رمز عبور</h4><input name="register_password" type="password" class="inputbox" v-model="register_data.password" /></div>
              <div><h4>۴ - تکرار رمز عبور</h4><input name="register_repassword" type="password" class="inputbox" v-model="register_repassword" /></div>
              <div><h4>۵ - کد امنیتی</h4><input name="register_captcha" type="text" class="inputbox" v-model="register_data.captcha_value" /></div>
              <div><h4>۶ - تصویر امنیتی</h4><img v-bind:src="'data:image/jpeg;base64,'+register_captcha" /></div>
              <div v-if="false">
                <h4>۷ : فعال‌سازی حالت ویژه عضویت در تالی</h4>
                <div class="checkbox_container" style="margin-bottom:0;">
                  <div class="checkbox"><input id="is_tally" v-model="is_tally" type="checkbox" class="toggle-checkbox" /><label for="is_tally" class="toggle-label" ></label></div>
                </div>
              </div>
              <div v-show="is_tally"><h4>۸ - نام فارسی فروشگاه در تالی</h4><input name="tally_name" type="text" class="inputbox" v-model="register_data.directRegisterParams.customerNameFa" /></div>
              <div v-show="is_tally" class="big_row"><h4>۹ - کد معرفی‌نامه تالی</h4><input name="tally_code" type="text" class="inputbox" v-model="register_data.directRegisterParams.forwardingToken" /></div>
              <div class="big_row"><h4>ثبت‌نام</h4><button v-on:click="register()">ثبت‌نام</button></div>

            </div>
            <div>
              <p>استفاده از سرویس اورکا برای کسب‌وکارهای کوچک اینترنتی کاملا رایگان خواهد بود. برای ثبت‌نام و دریافت اطلاعات کاربری می‌توانید از طریق فرم زیر اقدام کنید.</p>
              <p>در صورت نیاز به استفاده از فضای داده بیشتر می‌توانید از طرح‌های پیشرفته معرفی شده در صفحه <a href="https://oreka.dolphinai.ir">اورکا</a> استفاده کنید. در همه این مراحل در صورت نیاز به مشاوره و هرگونه راهنمایی می‌توانید از طریق ایمیل plugin@oreka.cloud اطلاعات تماس خود را برای ما ارسال کنید. کارشناسان ما در اسرع وقت آماده پاسخگویی به شما هستند.</p>
              <p>پس از ثبت‌نام ، تنظیمات اتصال به پست الکترونیک درج شده در فرم ثبت‌نام ارسال خواهد شد. شناسه کاربری وارد شده نیز باید به صورت انگلیسی و بدون اعداد و کاراکترهای ویژه درج شود.</p>
              <p>توجه : فیلد نام مشتری فقط باید شامل حروف انگلیسی کوچک و بین ۳ تا ۲۰ حرف باشد.</p>
              <p>توجه : رمز عبور باید حداقل ۱۰ کاراکتر و شامل حداقل یک رقم، یک حرف بزرگ، یک حرف کوچک و یکی از کاراکتر های @ $ ! % * ? & # ^ _ ~ - ( ) . , باشد</p>
            </div>
          </div>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 3">
          <h3>۱ - نام مشتری در سرویس اورکا</h3>
          <div class="input_container">
            <div class="input"><input name="customer_id" v-model="oreka.customer_id" type="text" class="inputbox ltr" /></div>
            <div class="note">شناسه مشتری خود را میتوانید در قسمت پروفایل در پنل اورکا مشاهده کنید.</div>
          </div>
          <h3>۲ - شناسه پروژه در سرویس اورکا</h3>
          <div class="input_container">
            <div class="input"><input name="project_id" v-model="oreka.project_id" type="text" class="inputbox ltr" /></div>
            <div class="note">شناسه پروژه خود را میتوانید در قسمت پروژه‌ها در پنل اورکا مشاهده کنید.</div>
          </div>
          <h3>۳ - شناسه کاتالوگ در سرویس اورکا</h3>
          <div class="input_container">
            <div class="input"><input name="catalog_id" v-model="oreka.catalog_id" type="text" class="inputbox ltr" /></div>
            <div class="note">شناسه کاتالوگ خود را میتوانید در داخل پروژه و در قسمت کاتالوگ‌‌ها در پنل اورکا مشاهده کنید.</div>
          </div>
          <h3>۴ - توکن امنیتی سرویس اورکا</h3>
          <div class="input_container">
            <div class="input"><input name="token" v-model="oreka.token" type="text" class="inputbox ltr" /></div>
            <div class="note">توکن امنیتی خود را میتوانید در قسمت مدیریت توکن‌های امنیتی در پنل اورکا مشاهده کنید.</div>
          </div>
          <div class="button_container">
            <button v-on:click="save_1()">ذخیره تنظیمات</button>
          </div>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 4">
          <h3>همگام‌سازی (sync) اطلاعات با سرویس اورکا</h3>
          <div class="action_container">
            <div class="note">با انجام همگام‌سازی تمامی اطلاعات مورد نیاز بین وردپرس و سرویس اورکا  همگام میشوند. توجه داشته باشید که در راه‌اندازی اولیه پلاگین حتما بعد از اتصال اولیه یک بار همگام‌سازی را انجام دهید.</div>
            <div class="action"><button v-on:click="ingest()" class="actionbox">شروع همگام‌سازی</button></div>
          </div>
          <h3>حذف اطلاعات از روی سرویس اورکا</h3>
          <div class="action_container">
            <div class="note">در صورت نیاز جهت حذف کامل اطلاعات از روی سرویس اورکا به جهت همگام‌سازی مجدد میتوانید از این گزینه استفاده کنید. توجه داشته باشید پس از حذف اطلاعات حتما برای یک بار همگام‌سازی کنید. به طور مثال بعد از فعال یا غیرفعال کردن حالت همخوانی با فروشگاه‌ساز ووکامرس به علت تغییر ساختار اطلاعات حتما باید یک بار اطلاات را حذف و مجدد همگام‌سازی کنید.</div>
            <div class="action"><button v-on:click="clear()" class="actionbox">حذف اطلاعات</button></div>
          </div>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 5">
          <h3>۱ - فعال‌سازی سرویس جستجوی هوشمند اورکا</h3>
          <div class="checkbox_container">
            <div class="checkbox"><input id="checkbox_1" v-model="oreka.activation" type="checkbox" class="toggle-checkbox" /><label for="checkbox_1" class="toggle-label" ></label></div>
            <div class="note">پس از فعال‌سازی سرویس اورکا جایگزین جستجوی پیشفرض وردپرس میشود.</div>
          </div>
          <h3>۲ : جایگزینی فرم جستجوی پیشفرض وردپرس با فرم جستجوی اورکا</h3>
          <div class="checkbox_container">
            <div class="checkbox"><input id="checkbox_2" v-model="oreka.form_replacement" type="checkbox" class="toggle-checkbox" /><label for="checkbox_2" class="toggle-label" ></label></div>
            <div class="note">پس از فعال‌سازی فرم جستجوی اورکا جایگزین فرم پیشفرض جستجوی وردپرس میشود. <a class="tooptip">( ? )<span>راهنمای تغییر ظاهر فرم جستجو جهت همخوانی با ظاهر قالب وردپرس در قسمت راهنما قابل مشاهده است.</span></a></div>
          </div>
          <h3>۳ : فعال‌سازی کادر نمایش پیشنهاد و نتایج جستجو اورکا به صورت ای‌جکس</h3>
          <div class="checkbox_container">
            <div class="checkbox"><input id="checkbox_3" v-model="oreka.ajax" type="checkbox" class="toggle-checkbox" /><label for="checkbox_3" class="toggle-label" ></label></div>
            <div class="note">پس از فعال‌سازی با شروع تایپ در فرم جستجوی اورکا ، کادر پیشنهاد در زیر فرم نمایش داده میشود. <a class="tooptip">( ? )<span>راهنمای تغییر ظاهر کادر پیشنهاد جهت همخوانی با ظاهر قالب وردپرس در قسمت راهنما قابل مشاهده است.</span></a></div>
          </div>
          <h3>۴ : فعال‌سازی پروسه همگام‌سازی اتوماتیک محتوا با سرویس اورکا</h3>
          <div class="checkbox_container">
            <div class="checkbox"><input id="checkbox_4" v-model="oreka.ingestion" type="checkbox" class="toggle-checkbox" /><label for="checkbox_4" class="toggle-label" ></label></div>
            <div class="note">پس از فعال‌سازی با درج پست/صفحه/محصول جدید در وردپرس ، سیستم اورکا به صورت خودکار همگام‌سازی خواهد شد.</div>
          </div>
          <h3>۵ : فعال‌سازی حالت همخوانی با فروشگاه‌ساز ووکامرس</h3>
          <div class="checkbox_container">
            <div class="checkbox"><input id="checkbox_5" v-model="oreka.woocommerce" type="checkbox" class="toggle-checkbox" /><label for="checkbox_5" class="toggle-label" ></label></div>
            <div class="note">پس از فعال‌سازی تنها محصولات جهت جستجو و همگام‌سازی مورد استفاده قرار  میگیرند. همچنین امکان استفاده از ابزارک فیلتر اورکا نیز فعال میشود.</div>
          </div>
          <h3>۶ : اضافه کردن جدول meta در زمان همگام‌سازی اطلاعات.</h3>
          <div class="checkbox_container">
            <div class="checkbox"><input id="checkbox_6" v-model="oreka.metatable" type="checkbox" class="toggle-checkbox" /><label for="checkbox_6" class="toggle-label" ></label></div>
            <div class="note">در صورتی که حالت همخوانی با فروشگاه‌ساز ووکامرس غیر فعال باشد جهت اضافه  کردن جدول meta به ساختار اطلاعات قابل جستجو از این گزینه استفاده کنید.</div>
          </div>
          <div class="button_container">
            <button v-on:click="save_2()">ذخیره تنظیمات</button>
          </div>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 6">
          <div class="msg show alert" v-if="oreka.woocommerce == false">این قسمت فقط در صورتی که همخوانی با فروشگاه‌ساز ووکامرس فعال شده باشد مورد استفاده قرار میگیرد</div>
          <h3>فیلترها در ووکامرس</h3>
          <div class="filters_container">
            <div class="filters_list_title">
              <div>عنوان فیلتر</div>
              <div>عنوان نمایشی</div>
              <div>نمایش فیلتر</div>
            </div>
            <div class="filters_list_title">
              <div>عنوان فیلتر</div>
              <div>عنوان نمایشی</div>
              <div>نمایش فیلتر</div>
            </div>
            <div v-for="(filter, filter_id) in oreka.filters" :key="filter_id" class="filters_list checkbox_container">
              <div><input type="text" class="inputbox" v-model="filter.name" readonly></div>
              <div><input type="text" class="inputbox" v-model="filter.title"></div>
              <div><div class="checkbox"><input :id="'checkbox_filter_' + filter_id" v-model="filter.show" type="checkbox" class="toggle-checkbox" /><label :for="'checkbox_filter_' + filter_id" class="toggle-label" ></label></div></div>
            </div>
          </div>
          <div class="button_container">
            <button v-on:click="save_3()">ذخیره تنظیمات</button>
          </div>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 7">
          <div class="msg show alert" v-if="oreka.form_replacement == true">این قسمت فقط در صورتی که جایگزینی فرم جستجو غیرفعال شده باشد مورد استفاده قرار میگیرد</div>
          <h3>شورت کد فرم جستجو</h3>
          <p>در صورتی که جایگزینی فرم جستجو غیرفعال شده باشد با استفاده از کد دستوری <code>[oreka_search_form]</code> می‌توانید فرم جستجوی اروکا را به صفحه مورد نظر خود اضافه کنید. توجه داشته باشید که این فرم جایگزین فرم پیشفرض وردپرس نخواهد شد. </p>
          <p>لازم به ذکر است این کد توانایی استفاده در قالب وردپرس را نیز دارد. لذا در صورت نیاز جهت استفاده در قالب سایت می‌توانید از این کد به صورت زیر استفاده کنید.</p>
          <p>نحوه استفاده در صفحات و ابزارک‌ها : <code>[oreka_search_form]</code></p>
          <p>نحوه استفاده در قالب سایت : <code><?php echo htmlspecialchars('<?php echo do_shortcode("[oreka_search_form]"); ?>'); ?></code></p>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 8">
        <h3>گزارش جستجو در اورکا</h3>
          <div class="report_container1">
            <div class="stat"><div>{{analytics.queryCount}}</div><div class="info">تعداد جستجو بر اساس عبارت</div></div>
            <div class="stat"><div>{{analytics.facetCount}}</div><div class="info">تعداد جستجو بر اساس فیلتر</div></div>
            <div class="stat"><div>{{analytics.noResultCount}}</div><div class="info">تعداد جستجوی بدون جواب</div></div>
            <div class="stat"><div>{{analytics.documentsCount}}</div><div class="info">تعداد اسناد قابل جستجو</div></div>
          </div>
          <div class="report_container2">
            <div class="stat"><div>آخرین عبارات جستجو شده</div><div class="info" v-for="(item,index) in analytics.lastSearches" :key="'ls_' + index">{{item.query}} (نتیجه : {{item.resultCount}})</div></div>
            <div class="stat"><div>آخرین فیلترهای جستجو شده</div><div class="info" v-for="(item,index) in analytics.lastFacets" :key="'lf_' + index">{{item.fixval}} (فیلتر‌ : {{item.name}})</div></div>
            <div class="stat"><div>جستجوهای محبوب</div><div class="info" v-for="(item,index) in analytics.statusByQuery" :key="'sq_' + index">{{item.query}} (تکرار : {{item.queryCount}})</div></div>
            <div class="stat"><div>جستجوهای فاقد نتیجه</div><div class="info" v-for="(item,index) in analytics.statusByNoResultQuery" :key="'snr_' + index">{{item.query}} (تکرار : {{item.queryCount}})</div></div>
          </div>
          <div class="report_container0">
            <div>از تاریخ :‌ <input type="date" name="the_date" v-model="analyticsDateFrom" /></div>
            <div>تا تاریخ :‌ <input type="date" name="the_date" v-model="analyticsDateTo" /></div>
            <div><button @click="getAnalytics()">نمایش گزارش</button></div>
          </div>
        </div>
        <!-- end tab -->
        <!-- start tab -->
        <div class="tab_content" v-if="tab == 9">
        <h3>راهنمای استفاده از سرویس جستجوی هوشمند اورکا</h3>
          <p>در صورتی‌که شما از طریقی غیر از پنل‌های ثبت‌نام اورکا به پلاگین اورکا دسترسی پیدا کرده باشید، لازم است تا اطلاعات کاربری خود جهت استفاده از پلاگین را از همکاران ما از طریق ایمیل plugin@oreka.cloud دریافت کنید. لطفا در ایمیل خود آدرس سایت و شماره تماس را ذکر کنید.</p>
          <p>اطلاعات دریافتی شما شامل توکن امنیتی، شناسه انحصاری پروژه، مشتری و کاتالوگ جهت درج در بخش تنظیمات اتصال داخل پنل پلاگین خواهد بود.</p>
          <p>درصورتی‌که اقدام به ثبت‌نام از طریق پنل اورکا کرده‌اید، می‌توانید با ورود به پنل اورکا، اطلاعات ذکر شده را از طریق منو‌ها کسب و در پنل پلاگین درج نمایید.</p>
          <p>پس از تنظیم کردن بخش تنظیمات اتصال، لازم است به بخش فعال‌سازی و تنظیمات در پنل پلاگین مراجعه کنید و بخش‌های مورد نظر را فعال کنید. این بخش‌ها به ترتیب به این شکل اثرگذار خواهند بود.</p>
          <p>در صورت فعال‌سازی «سرویس جستجوی هوشمند اورکا»، جستجوی پیش فرض وردپرس جای خود را جستجوی اورکا می‌دهد و از این پس، درخواست جستجو به جای وردپرس برای اورکا ارسال خواهد شد.</p>
          <p>در صورت فعال‌سازی «جایگزینی فرم جستجوی پیشفرض وردپرس با فرم جستجوی اورکا» ظاهر نوار جستجوی شما در سایت جای خود را به نوارجستجوی مخصوص اورکا خواهد داد. ما متعقدیم رعایت برخی موارد ظاهری در نوار جستجو منجر به بهبود تجربه کاربری خواهد شد. اما انتخاب این مورد کاملا به تصمیم شما بستگی دارد. درصورتی‌که این مورد غیرفعال باشد بازهم نتایج جستجو توسط اورکا تهیه و ارسال خواهد شد و تاثیری در خروجی نتایج جستجو نخواهد داشت.</p>
          <p>در صورت فعال‌سازی «فعال‌سازی کادر نمایش پیشنهاد و نتایج جستجو اورکا به صورت ای‌جکس» امکاناتی نظیر پیشنهاد تکمیل کوئری جستجو، اصلاح کوئری جستجو هنگام نوشتن متن توسط کاربر و پیشنهاد محصولات مرتبط با جستجو فعال خواهد شد. درصورتی‌که این مورد غیرفعال باشد، باز هم نتایج جستجو توسط اورکا تهیه و ارسال خواهد شد. با این تفاوت که این‌بار نتایج فقط پس از کلیک کردن بر روی جستجو و یا فشردن کلید Enter نمایش داده خواهد شد و قبل از آن پیشنهادی به کاربر ارائه نخواهد شد.</p>
          <p>در صورت «فعال‌سازی پروسه همگام‌سازی اتوماتیک محتوا با سرویس اورکا» اطلاعات سایت شما به صورت خودکار و دائمی به روزرسانی خواهد شد و اگر به عنوان مثال تغییری در موجودی یک محصول ایجاد شود و یا محصول جدیدی به سایت شما اضافه شود، اطلاعات آن جهت نمایش در نتایج جستجو ایندکس خواهد شد. اگر این مورد غیرفعال باشد لازم است که بعد از به‌روزرسانی داده‌های سایت خود، هربار به صورت دستی و با استفاده از تنظیمات بخش «همگام‌سازی اطلاعات» اقدام به به روزرسانی اطلاعات موتورجستجوی اورکا کنید.</p>
          <p>در صورت «فعال‌سازی حالت همخوانی با فروشگاه‌ساز ووکامرس» اطلاعات اضافی استفاده شده در ساختار داده‌ای ووکامرس به صورت خودکار با اطلاعات پست‌های محصولی شما تجمیع خواهد شد و نتیجه جستجوی بهتری به همراه خواهد داشت. <b>از این گزینه فقط هنگامی استفاده کنید که از ووکامرس برای ساخت فروشگاه اینترنتی خود استفاده می‌کنید. در غیر این صورت هنگام واکشی اطلاعات و ایندکس کردن به خطا خواهید خورد.</b> در صورتی‌که از موارد دیگری غیر از ووکامرس استفاده می‌کنید، این گزینه را غیرفعال کنید.</p>
          <p>در آخر لازم است یادآوری کنیم که اگر برای اولین بار از این پلاگین استفاده می‌کنید و یا مدتی پلاگین را غیرفعال کرده‌اید، بهتر است که پس از انجام تنظیمات بالا به بخش همگام‌سازی اطلاعات رفته و یک‌بار به صورت دستی اطلاعات سایت خود را به روز‌رسانی کنید.</p>
          <p>با آرزوی موفقیت</p>
        </div>
        <!-- end tab -->
      </div>
    </div>
  </div>
</div>