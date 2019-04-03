import 'rxjs/add/operator/toPromise';
import { HttpClient, HttpParams } from '@angular/common/http';
import { ToastController } from 'ionic-angular';
import { Injectable } from '@angular/core';

@Injectable()
export class MessagingProvider 
{
	constructor(
		private http: HttpClient, 
		public toastCtrl: ToastController) 
	{}

	sendWhatsapp(phone: string, message: string)
	{
    let standarUnivNumber = '62' + phone.slice(1);
		window.open('https://api.whatsapp.com/send?phone='+standarUnivNumber+'&text='+message);
	}

	sendSMS(phone: string, message: string)
	{
		let seq = this.prepareSMS(phone, message).share();
		seq.subscribe((res: any) => {
			let sms = res.error;
			if(sms == 0) 
			{
				let toast = this.toastCtrl.create({
					message: res.success,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
			else
			{
				let toast = this.toastCtrl.create({
					message: sms,
					duration: 3000,
					position: 'top'
				});
				toast.present();
			}
	  }, err => {});

	  return seq;
	}

	prepareSMS(phone: string, message: string, reqOpts?: any) 
	{
	    reqOpts = {
	      headers: {
	        'Content-Type': 'application/x-www-form-urlencoded',
	        'X-Requested-With': 'XMLHttpRequest'
	      },
	      params: new HttpParams()
	    };

	    let param = 'username=085759000374&password=0fbd9564d57513a3bef47775f11cfce3&passwordencrypt=1&number='+phone+'&message=OTP BUMDES '+message.replace(/\n/g, ", ")
	    return this.http.post('http://www.mpssoft.co.id/smsgateway/api/sendsms', param, reqOpts);
 	}

	sendEmail(phone: string, message: string, reqOpts?: any) 
	{
	    reqOpts = {
	      headers: {
	        'Content-Type': 'application/x-www-form-urlencoded',
	        'X-Requested-With': 'XMLHttpRequest'
	      },
	      params: new HttpParams()
	    };

	    let param = 'username=085759000374&password=0fbd9564d57513a3bef47775f11cfce3&passwordencrypt=1&number='+phone+'&message='+message.replace(/\n/g, ", ")
	    return this.http.post('http://www.mpssoft.co.id/smsgateway/api/sendsms', param, reqOpts);
 	}
}
