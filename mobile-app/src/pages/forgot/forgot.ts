import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavController, ToastController } from 'ionic-angular';
import { User, Online } from '../../providers/providers';
import { OtpPage } from '../pages';

@IonicPage()
@Component({
  selector: 'page-forgot',
  templateUrl: 'forgot.html',
})
export class ForgotPage 
{
  account: {email: string, phone: string} = {
    email: '',
    phone: ''
  };

  // Our translated text strings
  private forgotErrorString: string;

  constructor(
    private online: Online, 
    public navCtrl: NavController,
    public user: User,
    public toastCtrl: ToastController,
    public translateService: TranslateService) 
  {
    this.translateService.get('FORGOT_ERROR').subscribe((value) => {
      this.forgotErrorString = value;
    })
  }

  // Attempt to login in through our User service
  doForgot() 
  {
    this.user.forgot({init:'buyer-reset', pack:this.account}).subscribe((resp) => {
      this.navCtrl.push(OtpPage, {paramRoots:{from:'forgot', push_to:'ChangepasswordPage', ...this.account}});
    }, 
    (err) => {
      let toast = this.toastCtrl.create({
        message: this.forgotErrorString,
        duration: 3000,
        position: 'top'
      });
      toast.present();
      this.online.checkOnline(false);
    });
  }

  login() 
  {
    this.navCtrl.push('LoginPage');
  }
}
