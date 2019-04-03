import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, AlertController, MenuController } from 'ionic-angular';
import { TranslateService } from '@ngx-translate/core';
import { User, SplitPane, Online } from '../../providers/providers';
import { SecondRunPage } from '../../pages/pages';
import { Storage } from '@ionic/storage';

@IonicPage()
@Component({
  selector: 'page-changepassword',
  templateUrl: 'changepassword.html',
})
export class ChangepasswordPage 
{
	splitStateBool: boolean;
	_paramRoots: string;

	private changePassErrorString: string;
	_okButton: string;

	ModelInput: { password: string, passwordagain: string } = { password:'', passwordagain:'' }

	constructor(
		private alertCtrl: AlertController,
		private storage: Storage,
		private navParams: NavParams, 
		private online: Online, 
		public splitState: SplitPane, 
		public menu: MenuController, 
		public navCtrl: NavController, 
		public translate: TranslateService, 
		public toastCtrl: ToastController, 
		public user: User) 
	{
		this.translate.get(['PASS_ERROR','OK_BUTTON']).subscribe((value) => {
			this.changePassErrorString = value.PASS_ERROR;
			this._okButton = value.OK_BUTTON;
		});
    	
    	this.splitStateBool = false;
    	this._paramRoots = this.navParams.get('paramRoots');
	}

	doChange()
	{
		let alert = this.alertCtrl.create({
			title: 'Ups...',
			subTitle: this.changePassErrorString,
			buttons: [this._okButton]
		});

		if(this.ModelInput.password == '' || this.ModelInput.passwordagain == '')
		{
			alert.present();
		}
		else
		{
			if(this.ModelInput.password == this.ModelInput.passwordagain)
			{
				this.user.change({init:'buyer-change', pack:{fieldForm:this.ModelInput}}).subscribe((resp) => {
					if(this._paramRoots['from'] == 'loginAccount')
					{
						let toast = this.toastCtrl.create({
							message: this.user._globalUserMessage,
							duration: 3000,
							position: 'top'
						});
						toast.present();
						this.navCtrl.popTo('AccountPage');
					}
					else
					{
						this.storage.get('loginToken').then((isToken) => {
							const push = isToken ? 'HomeTabPage' : 'LoginPage';
							const menu = isToken ? true : false;
							this.navCtrl.setRoot(SecondRunPage, {}, {
								animate:true
							})
							.then(() => {
								this.splitState.interuptSplitState(this.splitStateBool);
								this.menu.enable(menu, 'sideMainMenu');
								this.navCtrl.push(push);
							})
				      	});
					}
				},
				(err) => {
					let toast = this.toastCtrl.create({
						message: this.changePassErrorString,
						duration: 5000,
						position: 'top'
					});
					toast.present();
					this.online.checkOnline(false);
				})
			}
			else
			{
				alert.present();
			}
		}
	}
}
