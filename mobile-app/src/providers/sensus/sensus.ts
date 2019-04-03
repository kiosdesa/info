import 'rxjs/add/operator/toPromise';
import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ToastController, LoadingController, AlertController } from 'ionic-angular';
import { Api } from '../api/api';

@Injectable()
export class SensusProvider 
{
	_sensusData: any;
	_globalMessage: any;
	_dataApprove: any;

	ErrorString: string;
	ErrorTitle: string;
	OkButton: string;

	constructor(
	    private api: Api, 
		public translate: TranslateService, 
		public loading: LoadingController, 
		public alert: AlertController, 
	    public toastCtrl: ToastController) 
	{
    	this.translate.get(['ERROR', 'ERROR_TITLE', 'OK_BUTTON']).subscribe((value) => {
      		this.ErrorString = value.ERROR;
			this.ErrorTitle = value.ERROR_TITLE;
			this.OkButton = value.OK_BUTTON;
		});
	}

	convertEndpoin(param: any)
	{
		return param.charAt(0).toUpperCase() + param.slice(1);
	}

	getNew(endpoin: string, indexParent?: string, param?: any)
	{
	    let seq = this.api.get(endpoin, param).share();
	    seq.subscribe((res: any) => {
	      let getNew = res[indexParent];
	      if(getNew.status.code == 200) 
	      {  
	        this._globalMessage = getNew.items.message;
	      }
	      else
	      {
	        let toast = this.toastCtrl.create({
	          message: getNew.items.message,
	          duration: 3000,
	          position: 'top'
	        });
	        toast.present();
	      }
	    }, err => {
				let alert = this.alert.create({
					cssClass: 'no-scroll',
					title: this.ErrorTitle,
					message: this.ErrorString,
					buttons: [
					{
						text: this.OkButton,
						role: 'cancel',
						handler: () => {}
					}]
				});
				alert.present();
			});
	    return seq;
	}

	add(param: any, indexParent?: string)
	{
		let seq = this.api.post('sensus', param).share();
		seq.subscribe((res: any) => {
			let addRes = res[indexParent];
			let itemsAdd = addRes.items;
			let toastColor = itemsAdd.approve == true ? 'success-toast' : 'warning-toast';
			let toast = this.toastCtrl.create({
				message: itemsAdd.message,
				cssClass: toastColor,
				duration: 3000,
				position: 'top'
			});

			this._dataApprove = itemsAdd.approve;
			
			if(addRes.status.code == 200) 
			{
				if(itemsAdd.approve) { toast.present(); }
				else { toast.present(); }
			} 
			else { toast.present(); }
		}, err => {
			let alert = this.alert.create({
				cssClass: 'no-scroll',
				title: this.ErrorTitle,
				message: this.ErrorString,
				buttons: [
				{
					text: this.OkButton,
					role: 'cancel',
					handler: () => {}
				}]
			});
			alert.present();
		});
		return seq;
	}

	change(param: any, indexParent?: string)
	{
		let seq = this.api.post('sensus', param).share();
		seq.subscribe((res: any) => {
			let changeRes = res[indexParent];
			let itemsChange = changeRes.items;
			let toastColor = itemsChange.approve == true ? 'success-toast' : 'warning-toast';
			let toast = this.toastCtrl.create({
				message: itemsChange.message,
				cssClass: toastColor,
				duration: 3000,
				position: 'top'
			});

			this._dataApprove = itemsChange.approve;
			
			if(changeRes.status.code == 200) 
			{
				if(itemsChange.approve) { toast.present(); }
				else { toast.present(); }
			} 
			else { toast.present(); }
		}, err => {});
		return seq;
	}

	remove(param: any, indexParent?: string)
	{
		let seq = this.api.post('sensus', param).share();
		seq.subscribe((res: any) => {
			let removeRes = res[indexParent];;
			let itemsRemove = removeRes.items;
			let toast = this.toastCtrl.create({
				message: itemsRemove.message,
				duration: 3000,
				position: 'top'
			});
			
			if(removeRes.status.code == 200) 
			{
				if(itemsRemove.approve){}
				else
				{ toast.present(); }
			} 
			else 
			{ toast.present(); }
		}, 
		err => {});
		return seq;
	}

	suspend(param: any)
	{}

	pushData(param: any) : void
	{
		this._sensusData = param;
	}
}
