<?php
Route::group(['namespace'=>'Yarm\Servicetools\Http\Controllers','prefix'=> strtolower(config('yarm.sys_name')),'middleware'=>['web']], function (){

    // Get comments on illustrations
    Route::get('/commentsOnIllustrations', 'ServiceToolsDataCleaningController@commentsOnIllustrationsList')
        ->name('comments_on_illustartions');
    // Get comments on translation
    Route::get('/commentsOnTranslation', 'ServiceToolsDataCleaningController@commentsOnTranslationList')
        ->name('comments_on_translation');

    // Get comments on preface_postface
    Route::get('/commentsOnPrefacePostface', 'ServiceToolsDataCleaningController@commentsOnPrefacePostfaceList')
        ->name('comments_on_preface_postface');

    // Get comments on publication
    Route::get('/commentsOnPublication', 'ServiceToolsDataCleaningController@commentsOnPublicationList')
        ->name('comments_on_publication');

    Route::post('/editCOP', 'ServiceToolsDataCleaningController@editCommentsOnPublication')
        ->name('edit_COP');

    Route::get('/getCOP', 'ServiceToolsDataCleaningController@getCommentsOnPublication')
        ->name('get_COP');

    // Get original title
    Route::get('/originalTitles', 'ServiceToolsDataCleaningController@originalTitleList')
        ->name('original_titles');

    // Get publishers
    Route::get('/publishers', 'ServiceToolsDataCleaningController@publisherList')
        ->name('publishers');

    Route::post('/confirmPublisher', 'ServiceToolsDataCleaningController@confirmPublisher')
        ->name('confirm_publisher');

    // Get files not found list
    Route::get('/filesNotFound', 'ServiceToolsDataCleaningController@fileNotFoundList')
        ->name('files_not_found_list');
    Route::get('/change_file_names', 'ServiceToolsDataCleaningController@changeFileNames')
        ->name('change_file_names');
    Route::post('/change_file', 'ServiceToolsDataCleaningController@changeOneFileName')
        ->name('change_file');

//routes to clean YARM-Core zoologie
//Route::get('CDZ_place', 'ServiceToolsDatacleaningController@CDZP');


//Routes for upload data
//Route::get('upload4dlbt', 'upload4dlbtController@upload4dlbt')->name('upload4dlbt');

//Upload refbase to yarmCore Todo changes for Laravel!!
//Route::get('uploadRefbase2yarmCore', 'refb2yarmCore');

//Route::get('addRefs2Groups', 'upload4dlbtController@addRefs2Groups')->name('addRefs2Groups');
//Route::get('hashRefs', 'upload4dlbtController@hashRefs')->name('hashRefs');
//Route::get('import_djack', '\upload4dlbtController@import_djack');
//Route::get('convert_dataLF', 'convertDataLFtoRIS@import_dletterenfonds');
//Route::get('moveLocation', 'moveLocation@moveLocation');
//Route::get('reloadTranslations', 'upload4dlbtController@reloadTranslations');
//Route::get('upload_places', 'upload4dlbtController@uploadPlaces');
//Route::get('change_file_names', 'upload4dlbtController@changeFileNames');
//Route::get('cleanOriginalTitle', 'CleanOriginalTitle@moveCommentsInOriginalTitle');
//Route::get('CleanCommentsOnIll', 'CleanCommentsOnIll@moveNamesIllustrator');
//Route::get('CleanCommentsPrPo', 'CleanCommentsOnPrPo@moveNamesPrefacePostface');
//Route::get('Move2Subtitle', 'Move2Subtitle@move2subtitle');
//Route::get('showDuplicates', 'showDuplicates@showDuplicates');
//Route::get('updateViaf', 'ServiceToolsDatacleaningController@updateViaf');
//Route::get('HashRefs', 'HashRefsController@Hash');

});
