import 'rxjs/add/operator/toPromise';
import { Injectable } from '@angular/core';
import { ToastController, LoadingController } from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { Api } from '../api/api';
//import { MessagingProvider } from '../messaging/messaging'

@Injectable()
export class User 
{
  _globalUserMessage: any = null;
  _globalUserData: any = null;

  constructor(
    private api: Api, 
    private storage: Storage,
    //private messaging: MessagingProvider,
    //public events: Events, 
    public loading: LoadingController,
    public toastCtrl: ToastController) 
  { }

  // Fungsi Daftar Petugas Baru
  signup(accountInfo: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    let seq = this.api.post('account', accountInfo).share();
    seq.subscribe((res: any) => {
      let addUsers = res.addBuyer;
      if(addUsers.status.code == 200) 
      {
        if(addUsers.items.approve)
        {
          this._loggedIn(addUsers.items.token);
        }
        else
        {
          this._loggedIn(null);
        }
         
        this._globalUserMessage = addUsers.items.message;
      }
      else
      {
        let toast = this.toastCtrl.create({
          message: addUsers.items.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, err => {
      loader.dismiss();
    });

    return seq;
  }


  // Fungsi LOGIN Petugas
  login(accountInfo: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    let seq = this.api.post('account', accountInfo).share();
    seq.subscribe((res: any) => {
      let loginUsers = res.loginBuyer
      let itemsUsers = loginUsers.items;
      if(loginUsers.status.code == 200) 
      {
        if(itemsUsers.approve)
        {
          this.storage.set('loginToken', itemsUsers.token);
          this._loggedIn(itemsUsers.data);
        }
        else
        {
          this._globalUserMessage = itemsUsers.message;
        }
      } 
      else 
      {
        let toast = this.toastCtrl.create({
          message: itemsUsers.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, 
    err => {
      loader.dismiss();
    });

    return seq;
  }


  // Fungsi LOGOUT Petugas
  logout(userParam: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    let seq = this.api.post('account', userParam).share();
    seq.subscribe((res: any) => {
      let logoutUsers = res.logoutBuyer;
      let itemsUsers = logoutUsers.items;
      if(logoutUsers.status.code == 200) 
      {
        if(itemsUsers.approve)
        {
          if(this.storage.get('loginToken')) this.storage.remove('loginToken');
          this._loggedIn(null);
          this._globalUserMessage = itemsUsers.approve;
        }
        else
        {
          this._globalUserMessage = itemsUsers.message;
        }
      } 
      else 
      {
        let toast = this.toastCtrl.create({
          message: itemsUsers.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, 
    err => {
      loader.dismiss();
    });

    return seq;
  }


  // Fungsi Verifikasi TOKEN
  checktoken(token: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    let seq = this.api.post('account', token).share();
    seq.subscribe((res: any) => {
      let tokenUsers = res.tokenBuyer;
      let itemsUsers = tokenUsers.items;
      if(tokenUsers.status.code == 200) 
      {
        this._loggedIn(itemsUsers.data);
        this._globalUserMessage = itemsUsers.message;
      } 
      else 
      {
        let toast = this.toastCtrl.create({
          message: itemsUsers.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, 
    err => {
      loader.dismiss();
    });
    return seq;
  }


  // Fungsi RESET PASSWORD Petugas
  forgot(inputParam: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    //let phoneNumber = inputParam.pack.phone;
    let seq = this.api.post('account', inputParam).share();
    seq.subscribe((res: any) => {
      if(res.resetBuyer.status.code == 200) 
      {
        if(this.storage.get('loginToken')) this.storage.remove('loginToken');
        this._loggedIn(null);
        this._globalUserMessage = res.resetBuyer.items.message;
        //if("sms" in res.resetBuyer.items){if(res.resetBuyer.items.sms != false) this.messaging.sendSMS(phoneNumber, res.resetBuyer.items.data.code_otp).subscribe();}
      } 
      else 
      {
        let toast = this.toastCtrl.create({
          message: res.loginBuyer.items.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, 
    err => {
      loader.dismiss();
    });

    return seq;
  }


  // Fungsi LOGOUT Petugas
  change(userParam: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    let seq = this.api.post('account', userParam).share();
    seq.subscribe((res: any) => {
      let changeUsers = res.changeBuyer;
      let itemsUsers = changeUsers.items;
      if(changeUsers.status.code == 200) 
      {
        this.storage.get('loginToken').then((thisToken) => {
          if(!thisToken) this.storage.set('loginToken', itemsUsers.token);
        });

        this._globalUserMessage = itemsUsers.message;
      } 
      else 
      {
        let toast = this.toastCtrl.create({
          message: itemsUsers.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, 
    err => {
      loader.dismiss();
    });

    return seq;
  }


  // Fungsi Verifikasi OTP
  otp(inputParam: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    let seq = this.api.post('account', inputParam).share();
    seq.subscribe((res: any) => {
      let otpUsers = res.otpBuyer;
      let itemsUsers = otpUsers.items;
      if(otpUsers.status.code == 200) 
      {
        if(itemsUsers.approve == true)
        {
          this._loggedIn(itemsUsers.data);
          this._globalUserMessage = itemsUsers.message;
        }
        else
        {
          this._globalUserMessage = itemsUsers.message;
        }
      } 
      else 
      {
        let toast = this.toastCtrl.create({
          message: itemsUsers.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, 
    err => {
      loader.dismiss();
    });

    return seq;
  }


  // Fungsi Get All Users
  self(inputParam: any) 
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    let seq = this.api.post('account', inputParam).share();
    seq.subscribe((res: any) => {
      let getUserData = res.selfBuyer;
      let userItems = getUserData.items;
      if(getUserData.status.code == 200) 
      {
        this._globalUserMessage = userItems.message;
      } 
      else 
      {
        let toast = this.toastCtrl.create({
          message: userItems.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
      loader.dismiss();
    }, 
    err => {
      loader.dismiss();
    });

    return seq;
  }


  _saveUserData(param)
  {
    this.storage.set('userData', param);
    //this.events.publish('user:logged', param);
  }


  // Fungsi Daftar Petugas Baru
  _loggedIn(paramLogged) 
  {
    this._globalUserData = paramLogged;
  }
}
