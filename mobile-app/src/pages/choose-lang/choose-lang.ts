import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, Events } from 'ionic-angular';
import { Settings } from './../../providers/providers';
import { FirstRunPage } from '../../pages/pages'

@IonicPage()
@Component({
  selector: 'page-choose-lang',
  templateUrl: 'choose-lang.html',
})
export class ChooseLangPage 
{
  constructor(
    private settings: Settings,
    public navCtrl: NavController, 
    public events: Events, 
    public navParams: NavParams) 
  {
    this.settings.load()
  }

  chooseLang(lang?:any)
  {
    this.settings.setValue('opt_lang', lang).then(() => {
      this.events.publish('lang:set', {lang:lang, page:FirstRunPage})
    });
  }
}
