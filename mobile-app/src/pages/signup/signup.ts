import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { LowerCasePipe } from '@angular/common';
import { IonicPage, NavController, ToastController, AlertController } from 'ionic-angular';
import { User } from '../../providers/providers';
//import { OtpPage } from '../pages';

@IonicPage()
@Component({
  selector: 'page-signup',
  templateUrl: 'signup.html',
  providers: [LowerCasePipe]
})
export class SignupPage 
{
  registFieldModel: { 
    user_name: string, 
    email: string, 
    password: string, 
    phone: string, 
    real_name: string,
    born_date: string
  } = {
    user_name: '',
    email: '',
    password: '',
    phone: '',
    real_name: '',
    born_date: ''
  };

  registFieldMockup = {
    user_name: 'ex: contoh_username',
    email: 'ex: test@example.com',
    password: '*******',
    phone: 'ex: 08x-xxxx-xxx',
    real_name: 'ex: si Kabayan',
    born_date: 'ex: 1993-12-31'
  };

  typeInputPass = 'password'

  // Our translated text strings
  private signupErrorString: string;
  private doneButton: string;
  private confirmTitle: string;
  _activeButton: boolean = false;

  constructor(
    private lowcasePipe: LowerCasePipe, 
    public navCtrl: NavController,
    public user: User,
    public toastCtrl: ToastController,
    public alert: AlertController, 
    public translateService: TranslateService) 
  {
    this.translateService.get(['SIGNUP_ERROR','DONE_BUTTON','CONFIRM_TITLE']).subscribe((value) => {
      this.signupErrorString = value.SIGNUP_ERROR;
      this.doneButton = value.DONE_BUTTON;
      this.confirmTitle = value.CONFIRM_TITLE;
    })
  }

  doSignup() 
  {
    // Attempt to login in through our User service
    this.user.signup({init:'buyer-add', pack:{fieldForm:this.registFieldModel}}).subscribe((res:any) => {
      const Resp = res.addBuyer.items;
      if("approve" in Resp)
      {
        if(Resp.approve != false)
        {
          let alert = this.alert.create({
            cssClass: 'no-scroll',
            title: this.confirmTitle,
            message: Resp.message,
            buttons: [{
              text: this.doneButton,
              role: 'cancel',
              handler: () => {
                this.navCtrl.push('LoginPage',{},{animate:true});
              }
            }]
          });
          alert.present();
          //this.navCtrl.push('ConfirmotpPage', {paramRoots:{from:'signup', email:this.registFieldMockup.email, push_to:'HomePageTab'}});
        }
        else
        {
          let toast = this.toastCtrl.create({
            message: Resp.message,
            cssClass: 'warning-toast',
            duration: 5000,
            position: 'top'
          });
          toast.present();
        }
      }
    }, 
    (err) => {
      let toast = this.toastCtrl.create({
        message: this.signupErrorString,
        cssClass: 'danger-toast',
        duration: 5000,
        position: 'top'
      });
      toast.present();
    });
  }

	sanitizeInput(val, key)
	{
	  const regexSanitizeI = /[^a-zA-Z0-9\_\-\s]/g;
	  const regexSanitizeII = /[^a-zA-Z0-9\@\.\_]/g;
	  const regexSanitizeIII = /[^a-zA-Z0-9\_]/g;
	  const regexSanitize = key == 'email' ? regexSanitizeII : (
      key == 'user_name' ? regexSanitizeIII : regexSanitizeI
    )
    val = key == 'user_name' ? this.lowcasePipe.transform(val) : val
	  this.registFieldModel[key] = val.replace(regexSanitize, '')
  }
  
  showPassword()
  {
    this.typeInputPass = 'text';
  }
}
