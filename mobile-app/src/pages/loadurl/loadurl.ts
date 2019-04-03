import { Component } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { IonicPage, NavController, NavParams } from 'ionic-angular';

@IonicPage()
@Component({
  selector: 'page-loadurl',
  templateUrl: 'loadurl.html',
})
export class LoadurlPage 
{
	currentParam: any;
	_urlOpen: any;
	_urlTitle: string;

	constructor(
		private sanitizer: DomSanitizer,
		public navCtrl: NavController, 
		public navParams: NavParams) 
	{
		this.currentParam = this.navParams.get('param');
		this._urlOpen = this.sanitizer.bypassSecurityTrustResourceUrl(this.currentParam.url);
		this._urlTitle = this.currentParam.title;
	}

	ionViewDidEnter() 
	{
		//window.open(this._urlOpen,'_self')
	}

}
