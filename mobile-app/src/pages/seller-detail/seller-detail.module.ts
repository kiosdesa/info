import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { SellerDetailPage } from './seller-detail';

@NgModule({
  declarations: [
    SellerDetailPage,
  ],
  imports: [
    IonicPageModule.forChild(SellerDetailPage),
    TranslateModule.forChild()
  ],
})
export class SellerDetailPageModule {}
