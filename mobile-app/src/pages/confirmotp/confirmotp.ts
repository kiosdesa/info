import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, MenuController, Platform } from 'ionic-angular';
import { TranslateService } from '@ngx-translate/core';
import { User, Online, SplitPane, CartProvider, RequestApiProvider } from '../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-confirmotp',
  templateUrl: 'confirmotp.html',
})
export class ConfirmotpPage 
{
  private otpErrorString: string;
  private otpMessage: string;
  private loginErrorString: string;
  _paramRoots: any;

  // Variable value input Model
  dataModelOTP: string;
  tmpSplitReturn: boolean;

  constructor(
    private api: RequestApiProvider, 
    private cart: CartProvider,
    private online: Online,
    public splitState: SplitPane, 
    public platform: Platform, 
    public navCtrl: NavController, 
    public menu: MenuController, 
    public translate: TranslateService, 
    public toastCtrl: ToastController, 
    public navParams: NavParams, 
    public user: User) 
  {
    this.translate.get(['OTP_ERROR', 'LOGIN_ERROR', 'OTP_MESSAGE']).subscribe((value) => {
      this.otpErrorString = value.OTP_ERROR;
      this.otpMessage = value.OTP_MESSAGE;
      this.loginErrorString = value.LOGIN_ERROR;
    });
    this.tmpSplitReturn = this.platform.width() > 900 ? true : null;
    this._paramRoots = navParams.get('paramRoots');
    //console.log(this._paramRoots)
  }

  // Attempt to login in through our User service
  doConfirm() 
  {
    const fields = {email:this._paramRoots['email'], otp:this.dataModelOTP, from:this._paramRoots['from']};
    this.user.otp({init:'buyer-otp', pack:{fieldForm:fields}})
    .subscribe((resp) => {
      if(this.user._globalUserData == null)
      {
        if(this._paramRoots['from'] == 'login')
        {
          let _ParamLogin:any = {};
          if("email" in this._paramRoots){ _ParamLogin['email'] = this._paramRoots['email'] }
          if("password" in this._paramRoots){ _ParamLogin['password'] = this._paramRoots['password'] }
          this.doLogin({init:'buyer-login', pack:_ParamLogin});
        }
        else
        {
          let toast = this.toastCtrl.create({
            message: this.user._globalUserMessage,
            duration: 3000,
            position: 'top'
          });
          toast.present();
        }
      }
      else
      {
        this.navCtrl.push(this._paramRoots['push_to'], {paramRoots:this._paramRoots['from']});
      }
    }, 
    (err) => {
      let toast = this.toastCtrl.create({
        message: this.otpErrorString,
        duration: 3000,
        position: 'top'
      });
      toast.present();
			this.online.checkOnline(false);
    });
  }

  // Attempt to login in through our User service
  doLogin(paramSet?:any) 
  {
    this.user.login(paramSet).subscribe((resp) => {
      if(this.user._globalUserData == null)
      {
        let toast = this.toastCtrl.create({
          message: this.user._globalUserMessage,
          duration: 5000,
          position: 'top'
        });
        toast.present();
      }
      else
      {
        this.cart.count();
        this.navCtrl.setRoot('HomeTabPage', {}, {
          animate: true,
          direction: 'forward'
        })
        .then(() => {
          this.splitState.interuptSplitState(this.tmpSplitReturn);
          this.menu.enable(true, 'sideMainMenu');
        });
      }
    }, 
    (err) => {
      let toast = this.toastCtrl.create({
        message: this.loginErrorString,
        cssClass: 'danger-toast',
        duration: 3000,
        position: 'top'
      });
      toast.present();
    });
  }

  async sendOTP()
  {
    const cluster = 'account';
    const endpoint = 'sendotpBuyer';
    const fields = {token:this._paramRoots['token'], phone:this._paramRoots['phone']};
    await this.api
    .post(cluster, endpoint, {init:'buyer-sendotp', pack:{fieldForm:fields}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint].items;
      if("response" in Resp)
      {
        if(Resp.response == true)
        {
          let toast = this.toastCtrl.create({
            message: this.otpMessage,
            duration: 3000,
            position: 'top'
          });
          toast.present();
        }
      }
    },
    (err)=>{
      let toast = this.toastCtrl.create({
        message: this.otpErrorString,
        cssClass: 'danger-toast',
        duration: 3000,
        position: 'top'
      });
      toast.present();
    })
  }
}
