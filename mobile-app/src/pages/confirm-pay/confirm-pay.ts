import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, ViewController, NavController, NavParams, Events, Platform, ToastController } from 'ionic-angular';
import { RequestApiProvider, Online } from '../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-confirm-pay',
  templateUrl: 'confirm-pay.html'
})
export class ConfirmPayPage 
{
	_dataParam: any = undefined;
	_dataMessage: any = undefined;
	_dataLoad: any = undefined;

	ErrorString: string;
	ErrorTitle: string;
	OkButton: string;

	constructor(
		private api: RequestApiProvider, 
		private online: Online, 
		public events: Events, 
		public view: ViewController, 
		public translate: TranslateService, 
		public toastCtrl: ToastController, 
    	public platform: Platform, 
		public navCtrl: NavController, 
		public navParams: NavParams) 
	{
    	this.translate.get(['ERROR', 'ERROR_TITLE', 'OK_BUTTON']).subscribe((value) => {
      		this.ErrorString = value.ERROR;
			this.ErrorTitle = value.ERROR_TITLE;
			this.OkButton = value.OK_BUTTON;
		});

		this._dataParam = this.navParams.get('paramRoots');
	}

	ionViewDidEnter()
	{
		this.loadConfirmFormat()
	}
	
	async loadConfirmFormat()
	{
		const endpoint = 'payconfirm';
		const _data = this._dataParam;
		const _dataJoin = _data.length > 1 ? _data.join('|') : _data;
		await this.api.get('app/' + endpoint, endpoint + 'App',{filter:_dataJoin}).subscribe((res)=>{
			const theItems = res[endpoint + 'App'].items;
			this._dataMessage = theItems.message;
			this._dataLoad = theItems.options;
		},
		(err)=>{
			this.online.checkOnline(false)
		})
	}

	openUrl(url:string, title: string, type: string)
	{
		this.navCtrl.pop()
		.then(()=>{
			if(type == 'achor')
			{
				window.open(url, "_blank");
			}
			else
			{
				if(type == 'frame') this.navCtrl.push('LoadurlPage', {param:{url:url, title:title}})
			}
		})
	}
}
