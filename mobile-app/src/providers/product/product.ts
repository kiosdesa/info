import 'rxjs/add/operator/toPromise';
import { ToastController, LoadingController } from 'ionic-angular';
import { Injectable } from '@angular/core';
import { Api } from '../api/api';

@Injectable()
export class ProductProvider 
{
  constructor(
	private api: Api,
	public loading: LoadingController, 
    public toastCtrl: ToastController) 
	{}

	filter(endpoint?:any, param?:any)
	{
		let seq = this.api.post('sensus', param).share();
		seq.subscribe((res:any) => {
			let Resp = res[endpoint + 'Product'];
			let items = Resp.items;
			let toast = this.toastCtrl.create({
				message: items.message,
				duration: 3000,
				position: 'top'
			});
			
			if(Resp.status.code !== 200) 
			{ toast.present() }
		}, 
		(err) => {});
		return seq;
	}

	detail(endpoint:any, param:any)
	{
	    let seq = this.api.post('sensus', param).share();
	    seq.subscribe((res:any) => {
			let product = res[endpoint];
			if(product.status.code !== 200) 
			{
				let toast = this.toastCtrl.create({
					message: product.items.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
	    }, (err) => {});

	    return seq;
	}

	interactTo(endpoint:any, param:any)
	{
		let seq = this.api.post('sensus', param).share();
		seq.subscribe((res:any) => {
			let Resp = res[endpoint + 'Product'];
			let items = Resp.items;
			let toast = this.toastCtrl.create({
				message: items.message,
				duration: 3000,
				position: 'top'
			});
			
			if(Resp.status.code !== 200) 
			{ toast.present() }
		}, 
		(err) => {});
		return seq;
	}

	interactFrom(cluster:any, endpoint:any, param:any)
	{
		let seq = this.api.post(cluster, param).share();
		seq.subscribe((res:any) => {
			let Resp = res[endpoint];
			let items = Resp.items;
			let toast = this.toastCtrl.create({
				message: items.message,
				duration: 3000,
				position: 'top'
			});
			
			if(Resp.status.code !== 200) 
			{ toast.present() }
		}, 
		(err) => {});
		return seq;
	}
}
