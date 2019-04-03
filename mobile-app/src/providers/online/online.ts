import { Injectable } from '@angular/core';
import { ToastController, LoadingController } from 'ionic-angular';
import { Api } from '../api/api';

@Injectable()
export class Online 
{
	_timeout: number = 13000;
	
	constructor(
		private api: Api,
		public toastCtrl: ToastController,
		public loading: LoadingController) 
	{}

	callStatus(load?:boolean) 
	{
		let loader = this.loading.create({
			spinner: 'dots',
			content: 'Loading...',
		});

		if(load != false) loader.present();

		let toast = this.toastCtrl.create({
			message: 'Failed to Connect!',
			cssClass: 'danger-toast', 
			position: 'top'
		});

		let seq = this.api.ping().share();
	    seq.subscribe((res: any)=>{
			if(res.code !== 200) 
			{
				setTimeout(()=>{
					toast.dismiss();
				}, this._timeout)
				if(load != false) loader.dismiss();
				if(load == false) this.loopingCheck(this._timeout,load);
			}
			else
			{
				toast.dismissAll();
			}

			if(load != false) loader.dismiss();
		}, 
		(err) => {
			toast.present();
			setTimeout(()=>{
				toast.dismiss();
			}, this._timeout)
			if(load != false) loader.dismiss();
			if(load == false) this.loopingCheck(this._timeout,load);
		});
		
		return seq;
	}

	checkOnline(loader?:boolean)
	{
		this.callStatus(loader).subscribe((res)=>{},(err)=>{
			this.loopingCheck(this._timeout,loader);
		});
	}

	loopingCheck(time?:number,loader?:boolean)
	{
		if(!time) time = 24000;
	    setTimeout(() => {
			this.callStatus(loader);
	    }, time);
	}
}