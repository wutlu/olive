<?php

Route::prefix('carousel-yonetimi')->group(function () {
    Route::get('/', 'CarouselController@carousels')->name('admin.carousels');
    Route::post('json', 'CarouselController@carouselsJson')->name('admin.carousels.json');
    Route::post('carousel', 'CarouselController@carousel')->name('admin.carousel');
    Route::put('carousel', 'CarouselController@carouselCreate');
    Route::patch('carousel', 'CarouselController@carouselUpdate');
    Route::delete('carousel', 'CarouselController@carouselDelete');
    Route::post('siralama', 'CarouselController@sortable')->name('admin.carousel.sortable');
});

# 
# borsa ayarları
#
Route::prefix('borsa-sorgulari')->group(function () {
    Route::get('/', 'BorsaController@queries')->name('borsa.queries');
    Route::post('{id}', 'BorsaController@getQuery')->name('borsa.query');
    Route::patch('update', 'BorsaController@updateQuery')->name('borsa.query.update');
});

Route::prefix('forum-yonetimi')->namespace('Forum')->group(function () {
    Route::prefix('kategori')->group(function () {
        Route::post('/', 'ForumController@categoryGet')->name('admin.forum.category');
        Route::put('/', 'ForumController@categoryCreate');
        Route::patch('/', 'ForumController@categoryUpdate');
        Route::delete('/', 'ForumController@categoryDelete');
    });
});

Route::prefix('sayfa-yonetimi')->group(function () {
    Route::get('/', 'PageController@adminListView')->name('admin.page.list');
    Route::get('sayfa/{id?}', 'PageController@adminView')->name('admin.page');
    Route::put('sayfa', 'PageController@adminCreate');
    Route::patch('sayfa', 'PageController@adminUpdate');
    Route::delete('sayfa', 'PageController@adminDelete');
});

Route::prefix('destek-talepleri')->group(function () {
    Route::get('{status?}', 'TicketController@adminList')->name('admin.tickets')->where('status', '(open|closed)');

    Route::get('talep/{id}', 'TicketController@adminView')->name('admin.ticket');
    Route::patch('talep/{id}/kapat', 'TicketController@adminClose')->name('admin.ticket.close');
    Route::put('talep/cevap', 'TicketController@adminReply')->name('admin.ticket.reply');
});

Route::prefix('muhasebe')->group(function () {
    Route::prefix('partner-odemeleri')->group(function () {
        Route::get('/', 'AccountingController@partnerPaymentsHistory')->name('admin.partner.history');
        Route::post('/', 'AccountingController@partnerPaymentsHistoryData');
        Route::post('duzenle', 'AccountingController@partnerPaymentsEdit')->name('admin.partner.payments.edit');
        Route::post('islem', 'AccountingController@partnerPaymentsAction')->name('admin.partner.payments.action');
    });

    Route::get('faturalar', 'AccountingController@invoices')->name('admin.invoices');
    Route::post('faturalar', 'AccountingController@invoicesData');
});

Route::prefix('kullanici-yonetimi')->group(function () {
    Route::get('/', 'UserController@adminListView')->name('admin.user.list');
    Route::post('json', 'UserController@adminListViewJson')->name('admin.user.list.json');
    Route::post('json/autocomplete', 'UserController@adminAutocomplete')->name('admin.user.autocomplete');

    Route::get('kullanici/{id}', 'UserController@adminView')->name('admin.user');
    Route::post('kullanici', 'UserController@adminCreate')->name('admin.user.register');
    Route::post('kullanici/{id}', 'UserController@adminUpdate');

    Route::get('kullanici/{id}/bildirimler', 'UserController@adminNotifications')->name('admin.user.notifications');
    Route::patch('kullanici/{id}/bildirim', 'UserController@adminNotificationUpdate')->name('admin.user.notification');

    Route::get('kullanici/{id}/fatura-gecmisi', 'UserController@adminInvoiceHistory')->name('admin.user.invoices');
    Route::get('kullanici/{id}/destek-talepleri', 'UserController@adminTickets')->name('admin.user.tickets');

    Route::post('kullanici/{id}/gsm-sifre', 'UserController@sendPasswordByGSM')->name('admin.user.password.gsm');

    Route::get('arama-gecmisi/{id}', 'UserController@adminSearchHistory')->name('admin.user.search_history');
    Route::post('arama-gecmisi/{id}', 'UserController@adminSearchHistoryData');
});

Route::prefix('organizasyon-yonetimi')->group(function () {
    Route::get('{status?}', 'OrganisationController@adminListView')->name('admin.organisation.list')->where('status', '(on|off)');
    Route::post('json', 'OrganisationController@adminListViewJson')->name('admin.organisation.list.json');

    Route::get('fiyat-ayarlari', 'OrganisationController@adminPriceSettings')->name('admin.organisation.price.settings');
    Route::post('fiyat-ayarlari', 'OrganisationController@adminPriceSettingsSave');

    Route::prefix('organizasyon')->group(function () {
        Route::get('{id}', 'OrganisationController@adminView')->name('admin.organisation');
        Route::post('{id}', 'OrganisationController@adminUpdate');
        Route::post('/', 'OrganisationController@adminCreate')->name('admin.organisation.create');

        Route::get('{id}/alarmlar', 'OrganisationController@alarms')->name('admin.organisation.alarms');
        Route::post('{id}/alarmlar', 'OrganisationController@alarmListJson');

        Route::get('{id}/arsivler', 'OrganisationController@pinGroups')->name('admin.organisation.archives');
        Route::post('{id}/arsivler', 'OrganisationController@pinGroupListJson');

        Route::get('{id}/fatura-gecmisi', 'OrganisationController@adminInvoiceHistory')->name('admin.organisation.invoices');
        Route::post('{id}/fatura-onay', 'OrganisationController@adminInvoiceApprove')->name('admin.organisation.invoice.approve');

        Route::get('{id}/kayitli-aramalar', 'OrganisationController@adminSavedSearches')->name('admin.organisation.saved_searches');
        Route::get('{id}/kayitli-aramalar/form/{search_id?}', 'OrganisationController@adminSavedSearch')->name('admin.organisation.saved_search');
        Route::post('{id}/kayitli-aramalar/form/{search_id?}', 'OrganisationController@adminSavedSearchSave');
    });
});

Route::prefix('kelime-hafizasi')->group(function () {
    Route::get('/', 'AnalysisController@dashboard')->name('analysis.dashboard');
    Route::get('{module}', 'AnalysisController@module')->name('analysis.module')->where('module', '('.implode('|', array_keys(config('system.analysis'))).')');

    Route::post('modul/test', 'AnalysisController@test')->name('analysis.module.test');

    Route::post('modul/kelimeler', 'AnalysisController@words')->name('analysis.module.words');
    Route::delete('modul/kelime/sil', 'AnalysisController@delete')->name('analysis.module.word.delete');
    Route::post('modul/kelime/kayit', 'AnalysisController@create')->name('analysis.module.word.create');
    Route::post('modul/kelime/tasi', 'AnalysisController@move')->name('analysis.module.word.move');

    Route::post('grup/derle', 'AnalysisController@compile')->name('analysis.group.compile');

    Route::patch('ogrenme-ayari', 'AnalysisController@learnSettings')->name('analysis.learn');
});

Route::delete('sil/{es_index}/{es_type}/{es_id}', 'ContentController@delete')->name('admin.content.delete');
