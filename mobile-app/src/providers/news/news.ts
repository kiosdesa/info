import 'rxjs/add/operator/toPromise';
import { HttpClient, HttpParams } from '@angular/common/http';
import { ToastController } from 'ionic-angular';
import { Injectable } from '@angular/core';

@Injectable()
export class Bumdesnews 
{
	_newsData: any[];

	constructor(
		public http: HttpClient, 
		public toastCtrl: ToastController) 
	{}

	get(query: string)
	{
		let seq = this.prepareGet(query).share();
		seq.subscribe((res: any) => {
			let newsStatus = res.status;
			if(newsStatus == 'ok') 
			{
				this.pushData(res.articles);
			}
			else
			{
				let toast = this.toastCtrl.create({
					message: newsStatus,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
	    }, err => {});

	    return seq;
	}

	prepareGet(query: string, reqOpts?: any) 
	{
	    reqOpts = {
	      headers: {
	        'Content-Type': 'application/x-www-form-urlencoded',
	        'X-Requested-With': 'XMLHttpRequest'
	      },
	      params: new HttpParams()
	    };

	    let param = 'q='+query+'&sortBy=publishedAt&apiKey=485393e7bf974d24bb851c33ce551f0e';
	    return this.http.get('https://newsapi.org/v2/everything?' + param, reqOpts);
 	}

 	pushData(data: any)
 	{
 		this._newsData = data;
 	}
}