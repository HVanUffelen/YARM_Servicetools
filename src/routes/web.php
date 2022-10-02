<?php
Route::group(['namespace'=>'Yarm\Servicetools\Http\Controllers','prefix'=>'dlbt','middleware'=>['web']], function (){

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


});
