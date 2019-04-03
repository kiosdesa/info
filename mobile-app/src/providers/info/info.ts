import 'rxjs/add/operator/toPromise';
import { ToastController } from 'ionic-angular';
import { Injectable } from '@angular/core';
import { Api } from '../api/api';

@Injectable()
export class InfoProvider 
{
	constructor(
		private api: Api,
		public toastCtrl: ToastController) 
	{}

	get()
	{
	    let seq = this.api.get('app/info').share();
	    seq.subscribe((res: any) => {
			let infoApp = res.infoApp;
			if(infoApp.status.code == 200) 
			{}
			else
			{
				let toast = this.toastCtrl.create({
					message: infoApp.items.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
	    }, err => {});

	    return seq;
	}
}