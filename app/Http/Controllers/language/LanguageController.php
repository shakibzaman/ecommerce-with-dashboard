<?php

namespace App\Http\Controllers\language;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use JoeDixon\Translation\Drivers\Translation;
use JoeDixon\Translation\Http\Requests\LanguageRequest;

class LanguageController extends Controller
{
  private $translation;

  public function __construct(Translation $translation)
  {
    $this->translation = $translation;
  }
  public function swap(Request $request, $locale)
  {
    if (!in_array($locale, ['en', 'fr', 'ar', 'de'])) {
      abort(400);
    } else {
      $request->session()->put('locale', $locale);
    }
    App::setLocale($locale);
    return redirect()->back();
  }

  public function test()
  {
    return $languages = $this->translation->allLanguages();
  }
}
