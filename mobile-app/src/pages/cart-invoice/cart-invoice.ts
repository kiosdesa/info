import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, ViewController, NavController, NavParams, Events, Platform, ToastController } from 'ionic-angular';
import { User, RequestApiProvider } from '../../providers/providers'

@IonicPage()
@Component({
  selector: 'page-cart-invoice',
  templateUrl: 'cart-invoice.html'
})
export class CartInvoicePage 
{
	_dataParam: any = undefined;
	_currencyFormat: string;
	_totalTariff: number;
	_totalToPay: number;
	_user: any;

	_openHow: string = undefined;

	constructor(
		private api: RequestApiProvider, 
		private user: User, 
		public events: Events, 
		public view: ViewController, 
		public translate: TranslateService, 
		public toastCtrl: ToastController, 
    	public platform: Platform, 
		public navCtrl: NavController, 
		public navParams: NavParams) 
	{
		let _paramRoots = this.navParams.get('paramRoots');
		this._dataParam = _paramRoots;
		this._currencyFormat = _paramRoots.data.total_ammount_format.symbol;
		this._totalTariff = _paramRoots.data.total_tariff;
		this._totalToPay = _paramRoots.data.total_ammount_format.value + _paramRoots.data.code_unique;
		this._user = this.user._globalUserData;
		//console.log(this.user._globalUserData)
	}

	goHome(emailing?:boolean)
	{
		this.view.dismiss().then(()=>{
			if(emailing == true)
			{
				const endpoint = 'emailOrders';
				this.api.post('transaction', endpoint, {init:'orders-email',pack:{fieldForm:{token:this._dataParam.data.token}}})
				.subscribe((res:any)=>{},(err)=>{})
			}
		});
	}

	confirm(p)
	{
		const _paramRoots = ("param" in p) ? p.param : {};
		this.navCtrl.push(p.page, {paramRoots:_paramRoots}, {animate:true});
	}

	openHow(message?:string)
	{
		if(this._openHow == undefined)
		{
			this._openHow = message;
		}
		else
		{
			this._openHow = undefined;
		}
	}
}
