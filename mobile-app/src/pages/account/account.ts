import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, MenuController, AlertController, ToastController, App } from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { TranslateService } from '@ngx-translate/core';
import { User, SplitPane, Online, NotifyProvider } from '../../providers/providers';
import { SecondRunPage } from '../pages';

interface accountData {
  field: string
  value: any
}
type FieldLists = accountData[]

@IonicPage()
@Component({
  selector: 'page-account',
  templateUrl: 'account.html',
})
export class AccountPage 
{
  	private logoutErrorString: string;
  	private logoutButton: string;
  	private confirmTitle: string;
  	private confirmMessage: string;
  	private cancelButton: string;

  	options: any;
	userData: any;
  	//form: FormGroup;

  	account: FieldLists;

	constructor(
		private user: User,
		private storage: Storage,
    	private translate: TranslateService,
		private alert: AlertController,
		private online: Online, 
		public notif: NotifyProvider, 
    	public splitState: SplitPane, 
    	public menu: MenuController, 
		public appCtrl: App,
		public navCtrl: NavController, 
		public navParams: NavParams,
    	public toastCtrl: ToastController) 
	{
	    this.storage.get('loginToken')
	    .then((isToken) => {
	    	this.getUserData(isToken);
		});

	    this.translate.get(['LOGOUT_ERROR', 'CONFIRM_TITLE', 'CONFIRM_MESSAGE', 'CANCEL_BUTTON', 'LOGOUT_BUTTON']).subscribe((value) => {
	      this.logoutErrorString = value.LOGOUT_ERROR;
		  this.logoutButton = value.LOGOUT_BUTTON;
		  this.confirmTitle = value.CONFIRM_TITLE;
		  this.confirmMessage = value.CONFIRM_MESSAGE;
		  this.cancelButton = value.CANCEL_BUTTON;
	    })
	}

	getUserData(Token: string): void
	{
		this.user.self({init:'buyer-self', pack:{token:Token}}).subscribe((res: any) => {
			this.userData = res.selfBuyer.items.data;
		},
		(err) => {
			this.userData = null;
		})
	}

	doUpdate()
	{
		this.account = this.userData;
		//console.log(this.account)
		this.user.change({init:'buyer-change', pack:{fieldForm:{trace:this.account}}}).subscribe((resp) => {
			let toast = this.toastCtrl.create({
				message: this.user._globalUserMessage,
				duration: 3000,
				position: 'top'
			});
			toast.present();
		},
		(err) => {
			let toast = this.toastCtrl.create({
				message: this.user._globalUserMessage,
				duration: 5000,
				position: 'top'
			});
			toast.present();
			this.online.checkOnline(false);
		})
	}

	editPassword()
	{
        this.navCtrl.push('ChangepasswordPage', {paramRoots:{from:'loginAccount'}});
    }

	doLogout() 
	{
		let alert = this.alert.create({
			cssClass: 'no-scroll',
			title: this.confirmTitle,
			message: this.confirmMessage,
			buttons: [
			{
				text: this.cancelButton,
				role: 'cancel',
				handler: () => {}
			},
			{
				text: this.logoutButton,
				handler: () => {
					let paramSet = {
						init: 'buyer-logout',
						pack: {}
					};

					this.user.logout(paramSet).subscribe((resp) => {
						this.splitState.interuptSplitState(false);
						this.menu.enable(false, 'sideMainMenu');
						this.appCtrl.getRootNav().setRoot(SecondRunPage,{},
						{
							animate:true,
							direction: 'enter'
						})
						.then(()=>{
							this.notif.removesAll();
						});
					}, 
					(err) => {
						let toast = this.toastCtrl.create({
						message: this.logoutErrorString,
						duration: 3000,
						position: 'top'
						});
						toast.present();
						this.online.checkOnline(false);
					});
				}
			}]
		});
		alert.present();
	}

	sanitizeInput(val, index)
	{
	  const regexSanitizeI = /[^a-zA-Z0-9\_\-\s]/g;
	  const regexSanitizeII = /[^a-zA-Z0-9\@\.\_]/g;
	  const regexSanitize = this.userData[index].field == 'email' ? regexSanitizeII : regexSanitizeI;
	  this.userData[index].value = val.replace(regexSanitize, '')
	}
}
