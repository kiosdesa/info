import { ToastController, LoadingController } from 'ionic-angular';
import { Injectable } from '@angular/core';
import { RequestApiProvider } from '../request-api/request-api';
import { NotifyProvider } from '../notify/notify';

@Injectable()
export class CartProvider 
{
	_cartCount:any = 0;

	constructor(
	private api: RequestApiProvider,
	public notif: NotifyProvider, 
	public loading: LoadingController, 
	public toastCtrl: ToastController) 
	{}

	async count()
	{
		await this.api.post('transaction','countsCarts', {init:'carts-counts',pack:{fieldForm:{}}})
		.subscribe((res:any)=>{
			let Resp = res['countsCarts'];
			let theItems = Resp.items;
			if(theItems.approve == true)
			{
				this.notif.store(theItems.count, 'cart');
				this._cartCount = theItems.count;
			}
			else
			{
				this._cartCount = 0;
			}
		}, (err)=>{
			this._cartCount = 0;
		})
	}
}