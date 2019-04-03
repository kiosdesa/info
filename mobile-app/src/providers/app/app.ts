import 'rxjs/add/operator/toPromise';
import { ToastController, LoadingController } from 'ionic-angular';
import { Injectable } from '@angular/core';
import { Api } from '../api/api';

@Injectable()
export class AppProvider 
{
	constructor(
		private api: Api,
		public loading: LoadingController, 
		public toastCtrl: ToastController) 
	{}

	/* Options: dash, home, graph, notify */
	data(options?: any)
	{
	    let seq = this.api.get('app/' + options).share();
	    seq.subscribe((res: any) => {
			let dataApp = res[options + 'App'];
			if(dataApp.status.code == 200) 
			{}
			else
			{
				let toast = this.toastCtrl.create({
					message: dataApp.items.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
	    }, err => {});

	    return seq;
	}

	filterproduct(endpoint?: any, param?: any)
	{
		console.log(endpoint, param);
		
		let loader = this.loading.create({
			spinner: 'dots',
			content: 'Loading...',
		});
		loader.present();
		let seq = this.api.get('app/'+endpoint, param).share();
		seq.subscribe((res: any) => {
			let dataApp = res[endpoint + 'App'];
			if(dataApp.status.code == 200) 
			{} 
			else 
			{}
			loader.dismiss();
		}, 
		err => {
			loader.dismiss();
		});
		return seq;
	}
    
	graphdetail(param: any)
	{
	    let seq = this.api.get('app/graphDetail', param).share();
	    seq.subscribe((res: any) => {
			let graphDetailApp = res.graphDetailApp;
			if(graphDetailApp.status.code == 200) 
			{}
			else
			{
				let toast = this.toastCtrl.create({
					message: graphDetailApp.items.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
	    }, err => {});

	    return seq;
	}
    
	announce(param: any)
	{
	    let seq = this.api.get('app/announce', param).share();
	    seq.subscribe((res: any) => {
			let announceApp = res.announceApp;
			if(announceApp.status.code == 200) 
			{}
			else
			{
				let toast = this.toastCtrl.create({
					message: announceApp.items.message,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
	    }, err => {});

	    return seq;
	}
}
