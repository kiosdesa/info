import { Component } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavParams, ToastController, App, NavController } from 'ionic-angular';
import { User, Settings } from '../../providers/providers';


@IonicPage()
@Component({
  selector: 'page-settings',
  templateUrl: 'settings.html'
})
export class SettingsPage 
{
  options: any;
  settingsReady = false;
  form: FormGroup;
  page: string = 'main';
  pageTitleKey: string = 'SETTINGS_TITLE';
  pageTitle: string;

  subSettings: any = SettingsPage;

  constructor(
    public navCtrl: NavController,
    public appCtrl: App,
    public user: User,
    public settings: Settings,
    public formBuilder: FormBuilder,
    public navParams: NavParams,
    public toastCtrl: ToastController,
    public translate: TranslateService) 
  {}

  _buildForm() 
  {
    let group: any = {
      opt_cache: [{value:this.options.opt_cache, disabled:true}],
      opt_lang: [this.options.opt_lang]
    };
    
    this.form = this.formBuilder.group(group);
    this.form.valueChanges.subscribe((v) => {
      this.settings.merge(this.form.value);
    });
  }

  ionViewDidLoad() 
  {
    // Build an empty form for the template to render
    this.form = this.formBuilder.group({});
  }

  ionViewWillEnter() 
  {
    this.form = this.formBuilder.group({});
    this.page = this.navParams.get('page') || this.page;
    this.pageTitleKey = this.navParams.get('pageTitleKey') || this.pageTitleKey;

    this.translate.get(this.pageTitleKey).subscribe((res) => {
      this.pageTitle = res;
    })

    this.settings.load().then(() => {
      this.settingsReady = true;
      this.options = this.settings.allSettings;
      this._buildForm();
    });
  }

  ngOnChanges() 
  {
    let toast = this.toastCtrl.create({
      message: 'All Changes',
      duration: 3000,
      position: 'top'
    });
    toast.present();
    //console.log('Ng All Changes');
  }

  openCredit()
  {
    this.navCtrl.push('CreditsPage');
  }
}
