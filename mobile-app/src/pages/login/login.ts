import { Component } from '@angular/core';
import { IonicPage, NavController, ToastController, MenuController, Platform } from 'ionic-angular';
import { TranslateService } from '@ngx-translate/core';
import { User, SplitPane, CartProvider } from '../../providers/providers';
import { HomeTab } from '../pages';

@IonicPage()
@Component({
  selector: 'page-login',
  templateUrl: 'login.html'
})
export class LoginPage 
{
  rootPage: any;
  tmpSplitReturn: boolean;

  account: {email: string, password: string} = {
    email: '',// 'sofandani@icloud.com',
    password: '' //'78910170'
  };

  paramSet = {
    init: 'buyer-login',
    pack: this.account
  };

  private loginErrorString: string;
  _activeOTP: boolean = false;
  _dataPush: any;

  constructor(
    public cart: CartProvider, 
    public navCtrl: NavController,
    public splitState: SplitPane, 
    public platform: Platform, 
    public menu: MenuController, 
    public user: User,
    public toastCtrl: ToastController,
    public translate: TranslateService) 
  {
    this.translate.get('LOGIN_ERROR').subscribe((value) => {
      this.loginErrorString = value;
    })

    this.tmpSplitReturn = this.platform.width() > 900 ? true : null;
  }

  // Attempt to login in through our User service
  doLogin() 
  {
    this.user.login(this.paramSet).subscribe((resp:any) => {
      this._dataPush = resp.loginBuyer.items;
      //if("phone" in resp.loginBuyer.items == true) this._userPhone = resp.loginBuyer.items.phone;
      if(this.user._globalUserData == null)
      {
        let _toastColor: string = '';
        if("active" in resp.loginBuyer.items == true)
        {
          _toastColor = 'info-toast';
          this._activeOTP = (resp.loginBuyer.items.active == false) ? true : false;
        }
        else
        {
          _toastColor = 'warning-toast';
          this._activeOTP = false;
        }
        let toast = this.toastCtrl.create({
          message: this.user._globalUserMessage,
          cssClass: _toastColor,
          duration: 5000,
          position: 'top'
        });
        toast.present();
      }
      else
      {
        this.cart.count();
        this.navCtrl.setRoot(HomeTab, {}, {
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
      this._activeOTP = false;
    });
  }

  openOTP(params)
  {
    this.navCtrl.push('ConfirmotpPage', {paramRoots:{from:'login', push_to:'HomeTabPage', ...params, ...this._dataPush}})
  }
}
